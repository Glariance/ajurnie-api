<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\Request;
use App\Mail\UserThankYouMail;
use App\Mail\AdminGoalNotification;
use Illuminate\Support\Facades\Mail;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'gender' => 'nullable|string|max:50',
            'age' => 'nullable|integer|min:0',
            'height' => 'nullable|integer|min:0',
            'current_weight' => 'nullable|numeric|min:0',
            'fitness_goal' => 'nullable|string|max:255',
            'target_weight' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'activity_level' => 'nullable|string|max:255',
            'workout_style' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string',
            'dietary_preferences' => 'nullable|array',
            'dietary_preferences.*' => 'string|max:255',
            'food_allergies' => 'nullable|string',
            'plan_generated' => 'boolean',
        ]);


        if (isset($validated['dietary_preferences']) && is_array($validated['dietary_preferences'])) {
            $validated['dietary_preferences'] = $validated['dietary_preferences'];
        }

        // Store in database
        $goal = Goal::create($validated);


        // Send emails
        if (empty($goal->email)) {
            throw new \Exception('User email is missing — cannot send user email.');
        }

        if (empty(config('mail.admin_email'))) {
            throw new \Exception('Admin email is missing — set ADMIN_EMAIL in .env.');
        }

        \Log::info('User email: ' . $goal->email);
        \Log::info('Admin email: ' . config('mail.admin_email'));

        Mail::to($goal->email)->send(new UserThankYouMail($goal));
        Mail::to(config('mail.admin_email'))->send(new AdminGoalNotification($goal));



        return response()->json([
            'message' => 'Goal saved and emails sent successfully!',
            'data' => $goal
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Goal $goal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Goal $goal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Goal $goal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Goal $goal)
    {
        //
    }
}
