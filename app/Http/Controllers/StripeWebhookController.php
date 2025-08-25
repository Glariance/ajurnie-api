<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Webhook;
use Log;
use App\Mail\WelcomeMail;
use App\Mail\SubscriptionActiveMail;
use App\Mail\PaymentFailedMail;
use Illuminate\Support\Facades\Mail;




class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret') // set in .env
            );
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        switch ($event->type) {
            // ✅ $1 setup fee payment succeeded
            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                $user = User::where('stripe_customer_id', $invoice->customer)->first();

                if ($user) {
                    // $1 setup fee payment
                    if ($invoice->total == 100) {
                        $user->is_paid = true;
                        $user->save();
                        Log::info("Setup fee paid by {$user->email}");
                        Mail::to($user->email)->send(new WelcomeMail($user));
                    }

                    // First subscription invoice (after trial with $1 discount)
                    if ($invoice->billing_reason === 'subscription_cycle' && $invoice->total > 100) {
                        Log::info("Subscription active for {$user->email}");
                        Mail::to($user->email)->send(new SubscriptionActiveMail($user));
                    }
                }
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $user = User::where('stripe_customer_id', $invoice->customer)->first();

                if ($user) {
                    Log::warning("Payment failed for {$user->email}");
                    Mail::to($user->email)->send(new PaymentFailedMail($user));
                }
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
