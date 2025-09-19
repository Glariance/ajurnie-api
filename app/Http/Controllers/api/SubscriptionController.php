<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Subscription as SubscriptionModel; // Eloquent model

use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Stripe\Customer as StripeCustomer;
use Stripe\PaymentMethod as StripePaymentMethod;

class SubscriptionController extends Controller
{
    /**
     * Map Stripe price IDs to human-readable plan names
     */
    private function planMapping(): array
    {
        return [
            // Founding Plans
            config('services.stripe.founding_novice_yearly')   => 'Novice - Founding (Yearly)',
            config('services.stripe.founding_trainer_yearly') => 'Trainer - Founding (Yearly)',

            // Post Founding Plans
            config('services.stripe.post_novice_monthly')  => 'Novice - Monthly',
            config('services.stripe.post_novice_yearly')   => 'Novice - Yearly',
            config('services.stripe.post_trainer_monthly') => 'Trainer - Monthly',
            config('services.stripe.post_trainer_yearly')  => 'Trainer - Yearly',
        ];
    }

    /**
     * Get current user's subscription info
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $sub  = $user->currentSubscription; // latest subscription row

        if (!$sub || !$sub->stripe_subscription_id) {
            return response()->json(['active' => false]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $remote = StripeSubscription::retrieve($sub->stripe_subscription_id);
            $price  = $remote->items->data[0]->price ?? null;

            // Fallbacks from local snapshot if Stripe response is partial
            $priceId = $price?->id ?? $sub->price_id;
            $planName = $this->planMapping()[$priceId] ?? $sub->plan ?? $priceId;

            $amount   = $price ? number_format($price->unit_amount / 100, 2) : null;
            $currency = $price ? strtoupper($price->currency) : null;

            return response()->json([
                'active'             => in_array($remote->status, ['active', 'trialing']),
                'status'             => $remote->status,
                'plan'               => $planName,
                'price'              => $amount && $currency ? ($amount . ' ' . $currency) : null,
                'start_date'         => $remote->start_date ? date('Y-m-d H:i:s', $remote->start_date) : null,
                'current_period_end' => $remote->current_period_end ? date('Y-m-d H:i:s', $remote->current_period_end) : null,
                'trial_end'          => $remote->trial_end ? date('Y-m-d H:i:s', $remote->trial_end) : null,
                'cancel_at'          => $remote->cancel_at ? date('Y-m-d H:i:s', $remote->cancel_at) : null,
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Stale/missing remote id → optionally mark local row as canceled
            if (($e->getHttpStatus() === 404) || ($e->getError()?->code === 'resource_missing')) {
                $sub->update(['status' => 'canceled', 'canceled_at' => now()]);
                return response()->json(['active' => false]);
            }
            return response()->json(['error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel current user's subscription immediately
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $sub  = $user->currentSubscription;

        if (!$sub || !$sub->stripe_subscription_id) {
            return response()->json(['error' => 'No active subscription'], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $remote = StripeSubscription::retrieve($sub->stripe_subscription_id);
            $remote->cancel(); // immediate cancel

            $sub->update([
                'status'      => $remote->status,
                'canceled_at' => now(),
            ]);

            return response()->json(['message' => 'Subscription cancelled successfully']);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // If already missing in Stripe, still mark as canceled locally
            if (($e->getHttpStatus() === 404) || ($e->getError()?->code === 'resource_missing')) {
                $sub->update([
                    'status'      => 'canceled',
                    'canceled_at' => now(),
                ]);
                return response()->json(['message' => 'Subscription marked canceled (stale id)']);
            }
            return response()->json(['error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Change plan immediately: attach PM, cancel old sub, create new sub, save new row
     */
    public function changePlan(Request $request)
    {
        $validated = $request->validate([
            'plan'           => 'required|string|in:novice,trainer',
            'interval'       => 'nullable|string|in:monthly,yearly',
            'payment_method' => 'required|string', // pm_xxx from Stripe.js
        ]);

        $user = $request->user();
        if (empty($user->stripe_customer_id)) {
            return response()->json(['error' => 'Customer not found for this user.'], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // Founding cutoff
        $isFounding = now()->lessThanOrEqualTo(\Carbon\Carbon::create(2025, 12, 31, 23, 59, 59));

        // Resolve price + interval
        if ($isFounding) {
            $interval = 'yearly';
            $priceId  = $validated['plan'] === 'novice'
                ? config('services.stripe.founding_novice_yearly')
                : config('services.stripe.founding_trainer_yearly');
            $membershipType = 'founding';
        } else {
            $interval = $validated['interval'] ?? 'monthly';
            if ($validated['plan'] === 'novice') {
                $priceId = $interval === 'monthly'
                    ? config('services.stripe.post_novice_monthly')
                    : config('services.stripe.post_novice_yearly');
            } else {
                $priceId = $interval === 'monthly'
                    ? config('services.stripe.post_trainer_monthly')
                    : config('services.stripe.post_trainer_yearly');
            }
            $membershipType = 'post_founding';
        }

        try {
            $pmId = $validated['payment_method'];

            // Attach PaymentMethod if needed & set default
            $pm = StripePaymentMethod::retrieve($pmId);

            if (!empty($pm->customer) && $pm->customer !== $user->stripe_customer_id) {
                return response()->json(['error' => 'Payment method belongs to a different customer.'], 400);
            }
            if (empty($pm->customer)) {
                $pm->attach(['customer' => $user->stripe_customer_id]);
            }
            StripeCustomer::update($user->stripe_customer_id, [
                'invoice_settings' => ['default_payment_method' => $pmId],
            ]);

            // Cancel old subscription (if present)
            $old = $user->currentSubscription;
            if ($old && $old->stripe_subscription_id) {
                try {
                    $remoteOld = StripeSubscription::retrieve($old->stripe_subscription_id);
                    $remoteOld->cancel();
                    $old->update(['status' => $remoteOld->status, 'canceled_at' => now()]);
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    if (($e->getHttpStatus() === 404) || ($e->getError()?->code === 'resource_missing')) {
                        $old->update(['status' => 'canceled', 'canceled_at' => now()]);
                    } else {
                        throw $e;
                    }
                }
            }

            // Create new subscription
            $newStripeSub = StripeSubscription::create([
                'customer' => $user->stripe_customer_id,
                'items'    => [['price' => $priceId]],
                'expand'   => ['latest_invoice.payment_intent'],
            ]);

            $currentPeriodEnd = $newStripeSub->current_period_end ?? null;
            if (!$currentPeriodEnd) {
                $fresh = \Stripe\Subscription::retrieve($newStripeSub->id);
                $currentPeriodEnd = $fresh->current_period_end ?? null;
            }

            SubscriptionModel::create([
                'user_id'                => $user->id,
                'stripe_subscription_id' => $newStripeSub->id,
                'price_id'               => $priceId,
                'plan'                   => $validated['plan'],
                'interval'               => $interval,
                'status'                 => $newStripeSub->status,
                'current_period_end'     => $currentPeriodEnd
                    ? \Carbon\Carbon::createFromTimestamp($currentPeriodEnd)
                    : null,
                'membership_type'        => $membershipType,
            ]);


            \Log::info('stripe sub', ['sub' => $newStripeSub]);


            return response()->json([
                'message'       => 'Plan updated successfully',
                'subscription'  => $newStripeSub,
                'membership_type' => $isFounding ? 'Founding' : 'Post Founding',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Plan change failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
