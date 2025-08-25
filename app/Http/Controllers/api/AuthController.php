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


class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json([
            'Welcome to the authentication API from index method'
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

            // ✅ Step 4: Pick plan price based on role
            $priceId = $validated['role'] === 'trainer'
                ? config('services.stripe.trainer_price_id')
                : config('services.stripe.novice_price_id');

            // ✅ Step 5: Trial period (2 minutes in test, 7 days in production)
            $trialEnd = app()->environment('production')
                ? now()->addDays(7)->timestamp   // real trial
                : now()->addMinutes(2)->timestamp; // quick testing trial

            $subscription = Subscription::create([
                'customer' => $customer->id,
                'items' => [['price' => $priceId]],
                'trial_end' => $trialEnd,
                'discounts' => [['coupon' => $coupon->id]], // $1 off first bill
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            // ✅ Step 6: Save user in DB
            $user = User::create([
                'fullname' => $validated['fullname'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'type' => User::ROLE_USER,
                'stripe_customer_id' => $customer->id,
                'stripe_subscription_id' => $subscription->id,
                'trial_ends_at' => date('Y-m-d H:i:s', $trialEnd),
            ]);

            DB::commit();

            $token = $user->createToken('token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'subscription' => $subscription,
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
