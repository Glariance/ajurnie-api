<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\InvoiceItem;





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
            'payment_method' => 'required|string', // stripe payment method id
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        // Create customer
        $customer = Customer::create([
            'email' => $validated['email'],
            'name'  => $validated['fullname'],
            'payment_method' => $validated['payment_method'],
            'invoice_settings' => [
                'default_payment_method' => $validated['payment_method'],
            ],

        ]);

        // ✅ Step 1: Charge $1 immediately
        InvoiceItem::create([
            'customer' => $customer->id,
            'amount' => 100, // $1 in cents
            'currency' => 'usd',
            'description' => 'Initial registration fee',
        ]);

        // Create and pay invoice immediately
        $invoice = \Stripe\Invoice::create([
            'customer' => $customer->id,
            'auto_advance' => true,
        ]);
        $invoice->pay();

        // ✅ Step 2: Create subscription with 7-day trial
        $priceId = $validated['role'] === 'trainer'
            ? config('services.stripe.trainer_price_id')
            : config('services.stripe.novice_price_id');

        $subscription = Subscription::create([
            'customer' => $customer->id,
            'items' => [['price' => $priceId]],
            'trial_period_days' => 7, // free trial
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        // Save user
        $user = User::create([
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'stripe_customer_id' => $customer->id,
            'stripe_subscription_id' => $subscription->id,
            'trial_ends_at' => now()->addDays(7),
        ]);

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'subscription' => $subscription,
        ], 201);
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
