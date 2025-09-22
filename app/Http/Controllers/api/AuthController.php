<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Subscription as SubscriptionModel; // ⬅️ Eloquent model

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

use App\Mail\SubscriptionActiveMail;
use App\Mail\WelcomeMail;
use App\Mail\ResetPasswordMail;

use Carbon\Carbon;

// Stripe SDK
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\InvoiceItem;
use Stripe\PaymentMethod;
use Stripe\Subscription as StripeSubscription;
use Stripe\Coupon;
use Stripe\Invoice;

class AuthController extends Controller
{
    /**
     * Just a simple ping.
     */
    public function index()
    {
        return response()->json([
            'Welcome to the authentication API from index method',
        ]);
    }

    /**
     * Register (create user + Stripe customer + subscription).
     * Persists subscription in `subscriptions` table (not on users).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullname'         => 'required|string|max:255',
            'email'            => 'required|string|email|unique:users,email',
            'password'         => 'required|string|min:6|confirmed',
            'role'             => 'required|string|in:novice,trainer',
            'payment_method'   => 'required|string',                 // pm_xxx from Stripe.js
            'interval'         => 'required|string|in:monthly,yearly',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));
        DB::beginTransaction();

        $customer = null;

        try {
            // 1) Create Stripe Customer
            $customer = Customer::create([
                'email' => $validated['email'],
                'name'  => $validated['fullname'],
            ]);

            // Attach payment method
            $pmId = $validated['payment_method'];
            $pm   = PaymentMethod::retrieve($pmId);

            if (!empty($pm->customer) && $pm->customer !== $customer->id) {
                return response()->json([
                    'error'   => 'Payment method belongs to a different customer.',
                    'message' => 'Please use a different card.',
                ], 400);
            }

            if (empty($pm->customer)) {
                $pm->attach(['customer' => $customer->id]);
            }

            Customer::update($customer->id, [
                'invoice_settings' => [
                    'default_payment_method' => $pmId,
                ],
            ]);

            // 2) $1 registration fee
            InvoiceItem::create([
                'customer'    => $customer->id,
                'amount'      => 100,
                'currency'    => 'usd',
                'description' => 'Initial registration fee',
            ]);

            $invoice = Invoice::create([
                'customer'     => $customer->id,
                'auto_advance' => true,
            ]);
            $invoice->pay();

            // 3) One-time $1 discount coupon
            $coupon = Coupon::create([
                'currency'   => 'usd',
                'amount_off' => 100,
                'duration'   => 'once',
            ]);

            // 4) Founding vs Post-Founding logic
            $isFounding = now()->lessThanOrEqualTo(Carbon::create(2025, 12, 31, 23, 59, 59));

            if ($isFounding) {
                $interval = 'yearly';
                $priceId  = $validated['role'] === 'novice'
                    ? config('services.stripe.founding_novice_yearly')
                    : config('services.stripe.founding_trainer_yearly');
                $memberType = 'founding';
            } else {
                $interval = $validated['interval'];
                if ($validated['role'] === 'novice') {
                    $priceId = $interval === 'monthly'
                        ? config('services.stripe.post_novice_monthly')
                        : config('services.stripe.post_novice_yearly');
                } else {
                    $priceId = $interval === 'monthly'
                        ? config('services.stripe.post_trainer_monthly')
                        : config('services.stripe.post_trainer_yearly');
                }
                $memberType = 'post_founding';
            }

            // 5) Trial period
            if (app()->environment('production')) {
                if ($isFounding) {
                    // Founding members → maybe no trial, immediate billing
                    $trialEnd = null;
                } else {
                    // Post-Founding members → 7-day trial
                    $trialEnd = now()->addDays(7)->timestamp;
                }
            } else {
                // Non-production (local/dev/staging) → short 2-minute trial for testing
                $trialEnd = now()->addMinutes(2)->timestamp;
            }

            // 6) Create Stripe Subscription
            $stripeSub = \Stripe\Subscription::create([
                'customer'  => $customer->id,
                'items'     => [['price' => $priceId]],
                'trial_end' => $trialEnd,
                'discounts' => [['coupon' => $coupon->id]],
                'expand'    => ['latest_invoice.payment_intent', 'items'],
            ]);

            // ✅ Fix: Resolve current_period_end properly
            $currentPeriodEnd = $stripeSub->current_period_end
                ?? ($stripeSub->items->data[0]->current_period_end ?? null);

            if (!$currentPeriodEnd) {
                $fresh = \Stripe\Subscription::retrieve($stripeSub->id, ['expand' => ['items']]);
                $currentPeriodEnd = $fresh->current_period_end
                    ?? ($fresh->items->data[0]->current_period_end ?? null);
            }

            // 7) Create User
            $user = User::create([
                'fullname'           => $validated['fullname'],
                'email'              => $validated['email'],
                'password'           => Hash::make($validated['password']),
                'role'               => $validated['role'],
                'type'               => User::ROLE_USER ?? 'user',
                'stripe_customer_id' => $customer->id,
            ]);

            // 8) Save subscription in subscriptions table
            SubscriptionModel::create([
                'user_id'                => $user->id,
                'stripe_subscription_id' => $stripeSub->id,
                'price_id'               => $priceId,
                'plan'                   => $validated['role'],
                'interval'               => $interval,
                'status'                 => $stripeSub->status,
                'trial_ends_at' => $trialEnd
                    ? Carbon::createFromTimestamp($trialEnd)
                    : null,
                'current_period_end'     => $currentPeriodEnd
                    ? Carbon::createFromTimestamp($currentPeriodEnd)
                    : null,
                'membership_type'        => $memberType,
            ]);

            DB::commit();

            // 9) Send emails
            try {
                Mail::to($user->email)->send(new WelcomeMail($user));
                Mail::to($user->email)->send(new SubscriptionActiveMail($user, $stripeSub));
            } catch (\Exception $mailError) {
                \Log::error("Mail sending failed: " . $mailError->getMessage());
            }

            // 10) Token + response
            $token = $user->createToken('token')->plainTextToken;

            return response()->json([
                'user'            => $user,
                'token'           => $token,
                'subscription'    => $stripeSub,
                'membership_type' => $isFounding ? 'Founding' : 'Post Founding',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($customer?->id)) {
                try {
                    $stripeCustomer = Customer::retrieve($customer->id);
                    $stripeCustomer->delete();
                } catch (\Exception $cleanupError) {
                    \Log::error("Stripe cleanup failed: " . $cleanupError->getMessage());
                }
            }

            return response()->json([
                'error'   => 'Registration failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lightweight user profile for your SPA.
     */
    public function getUser(Request $request)
    {
        $u = $request->user();

        return response()->json([
            'id'        => $u->id,
            'fullname'  => $u->fullname,
            'email'     => $u->email,
            'phone'     => $u->phone,
            'dob'       => $u->dob, // format via model cast if you prefer
            'gender'    => $u->gender,
            'address1'  => $u->address1,
            'address2'  => $u->address2,
            'city'      => $u->city,
            'state'     => $u->state,
            'zip'       => $u->zip,
            'country'   => $u->country,
            'bio'       => $u->bio,
            'avatarUrl' => $u->avatar ? Storage::url($u->avatar) : null,
        ]);
    }

    /**
     * Partial user update (no email change unless explicitly allowed).
     */
    public function updateUser(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'fullname' => ['sometimes', 'required', 'string', 'max:255'],
            'phone'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'dob'      => ['sometimes', 'nullable', 'date'],
            'gender'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'address1' => ['sometimes', 'nullable', 'string'],
            'address2' => ['sometimes', 'nullable', 'string'],
            'city'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'state'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'zip'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'country'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio'      => ['sometimes', 'nullable', 'string'],
            'avatar'   => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Handle avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->fill($validated)->save();

        return response()->json([
            'id'        => $user->id,
            'fullname'  => $user->fullname,
            'email'     => $user->email,
            'phone'     => $user->phone,
            'dob'       => $user->dob,
            'gender'    => $user->gender,
            'address1'  => $user->address1,
            'address2'  => $user->address2,
            'city'      => $user->city,
            'state'     => $user->state,
            'zip'       => $user->zip,
            'country'   => $user->country,
            'bio'       => $user->bio,
            'avatarUrl' => $user->avatar ? Storage::url($user->avatar) : null,
        ]);
    }

    /**
     * Login → returns API token + user.
     */
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token
        ], 200);
    }

    public function user(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }

    /**
     * Change password (simple).
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    /**
     * Minimal profile update endpoint (if you keep it).
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|string|email|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();

        if (isset($validated['fullname'])) {
            $user->fullname = $validated['fullname'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user
        ], 200);
    }

    /**
     * Forgot password → send reset link (SPA flow).
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'message' => "We couldn't find an account with that email. Double-check the address or create a new one."
            ], 404);
        }

        $token = Password::createToken($user);

        $spa = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $url = $spa . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($user->email);

        Mail::to($user->email)->send(new ResetPasswordMail($user, $url));

        return response()->json(['message' => 'If that email exists, a reset link was sent.']);
    }

    /**
     * Reset password (SPA flow).
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password updated.'])
            : response()->json(['message' => __($status)], 422);
    }
}
