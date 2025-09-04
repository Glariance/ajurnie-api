<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Price;

class SubscriptionController extends Controller
{
    /**
     * Map Stripe price IDs to human-readable plan names
     */
    private function planMapping()
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
        if (!$user->stripe_subscription_id) {
            return response()->json(['active' => false]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $subscription = Subscription::retrieve($user->stripe_subscription_id);

            // Get price object from subscription
            $price = $subscription->items->data[0]->price;
            $priceId = $price->id;

            // Map price ID → plan name
            $planName = $this->planMapping()[$priceId] ?? $priceId;

            // Format price
            $amount = number_format($price->unit_amount / 100, 2);
            $currency = strtoupper($price->currency);

            return response()->json([
                'active' => in_array($subscription->status, ['active', 'trialing']),
                'status' => $subscription->status,
                'plan' => $planName,
                'price' => $amount . ' ' . $currency, // ✅ Added price
                'start_date' => date('Y-m-d H:i:s', $subscription->start_date),
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end),
                'trial_end' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
                'cancel_at' => $subscription->cancel_at ? date('Y-m-d H:i:s', $subscription->cancel_at) : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Cancel current user's subscription (end of billing cycle)
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        if (!$user->stripe_subscription_id) {
            return response()->json(['error' => 'No active subscription'], 400);
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Cancel immediately
            $subscription = \Stripe\Subscription::retrieve($user->stripe_subscription_id);
            $subscription->delete();

            $user->update(['subscription_status' => $subscription->status]);

            return response()->json(['message' => 'Subscription cancelled successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
