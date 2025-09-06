<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\InvoiceItem;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionActiveMail;
use App\Mail\WelcomeMail;



class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json([
            'Welcome to the authentication API from index method',
            // app()->environment()
        ]);
    }


    public function getUser()
    {
        //
        return response()->json([

            'users' => User::all()

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:novice,trainer',
            'payment_method' => 'required|string',
            'interval' => 'string|in:monthly,yearly', // 👈 new field to pick interval
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        DB::beginTransaction();

        try {
            // ✅ Step 1: Create Stripe customer
            $customer = Customer::create([
                'email' => $validated['email'],
                'name'  => $validated['fullname'],
                'payment_method' => $validated['payment_method'],
                'invoice_settings' => [
                    'default_payment_method' => $validated['payment_method'],
                ],
            ]);

            // ✅ Step 2: Charge $1 upfront (registration fee)
            InvoiceItem::create([
                'customer' => $customer->id,
                'amount' => 100, // $1
                'currency' => 'usd',
                'description' => 'Initial registration fee',
            ]);

            $invoice = \Stripe\Invoice::create([
                'customer' => $customer->id,
                'auto_advance' => true,
            ]);
            $invoice->pay();

            // ✅ Step 3: Create one-time $1 discount coupon
            $coupon = \Stripe\Coupon::create([
                'currency' => 'usd',
                'amount_off' => 100,   // $1 off
                'duration' => 'once',  // apply only to first subscription invoice
            ]);

            // ✅ Step 4: Determine if user is Founding or Post Founding
            $today = now();
            $cutoff = \Carbon\Carbon::create(2025, 12, 31, 23, 59, 59);
            $isFounding = $today->lessThanOrEqualTo($cutoff);

            // ✅ Step 5: Pick correct price ID
            if ($isFounding) {
                // Founding Members → only yearly available
                if ($validated['role'] === 'novice') {
                    $priceId = config('services.stripe.founding_novice_yearly');
                } else {
                    $priceId = config('services.stripe.founding_trainer_yearly');
                }
            } else {
                // Post Founding Members → both monthly & yearly available
                if ($validated['role'] === 'novice') {
                    $priceId = $validated['interval'] === 'monthly'
                        ? config('services.stripe.post_novice_monthly')
                        : config('services.stripe.post_novice_yearly');
                } else {
                    $priceId = $validated['interval'] === 'monthly'
                        ? config('services.stripe.post_trainer_monthly')
                        : config('services.stripe.post_trainer_yearly');
                }
            }

            // ✅ Step 6: Trial period
            // $trialEnd = app()->environment('production')
            //     ? now()->addDays(7)->timestamp
            //     : now()->addMinutes(2)->timestamp;

            $trialEnd = now()->addMinutes(2)->timestamp;

            $subscription = Subscription::create([
                'customer' => $customer->id,
                'items' => [['price' => $priceId]],
                'trial_end' => $trialEnd,
                'discounts' => [['coupon' => $coupon->id]],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            // ✅ Step 7: Save user in DB
            $user = User::create([
                'fullname' => $validated['fullname'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'type' => User::ROLE_USER,
                'stripe_customer_id' => $customer->id,
                'stripe_subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'subscription_price_id' => $priceId,
                'subscription_interval' => $validated['interval'],
                'trial_ends_at' => date('Y-m-d H:i:s', $trialEnd),
            ]);

            DB::commit();

            // ✅ Step 8: Send Emails
            try {
                Mail::to($user->email)->send(new WelcomeMail($user));
                Mail::to($user->email)->send(new SubscriptionActiveMail($user, $subscription));
            } catch (\Exception $mailError) {
                \Log::error("Mail sending failed: " . $mailError->getMessage());
            }

            $token = $user->createToken('token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'subscription' => $subscription,
                'membership_type' => $isFounding ? 'Founding' : 'Post Founding',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($customer->id)) {
                try {
                    $stripeCustomer = \Stripe\Customer::retrieve($customer->id);
                    $stripeCustomer->delete();
                } catch (\Exception $cleanupError) {
                    \Log::error("Stripe cleanup failed: " . $cleanupError->getMessage());
                }
            }

            return response()->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }





    public function login(Request $request)
    {


        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'user' => $user,
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


    public function changePassword(Request $request)
    {

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();

        if (isset($validated['fullname'])) {
            $user->fullname = $validated['fullname'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
