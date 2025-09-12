<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\Request;
use App\Mail\UserThankYouMail;
use App\Mail\AdminGoalNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\PlanPdf; // <-- table for storing PDF info
use Barryvdh\DomPDF\Facade\Pdf; // using dompdf

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
            'user_id' => 'nullable|integer|exists:users,id',
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

        $userInfo = "
                Name: {$goal->name}
                Age: {$goal->age}
                Gender: {$goal->gender}
                Height: {$goal->height} cm
                Current Weight: {$goal->current_weight} kg
                Target Weight: {$goal->target_weight} kg
                Goal: {$goal->fitness_goal}
                Activity Level: {$goal->activity_level}
                Workout Style: {$goal->workout_style}
                Dietary Preferences: " . implode(', ', (array)$goal->dietary_preferences) . "
                Allergies: {$goal->food_allergies}
                Medical Conditions: {$goal->medical_conditions}
                ";

        $prompt = "You are a certified fitness & nutrition expert. 
                Based on the following user profile, create a safe, practical 
                4-week gym + nutrition plan. Include workouts (per week), 
                daily diet recommendations, and safety notes. 
                Keep language simple and motivational.\n\n{$userInfo}";

        // -------------------------------
        // 2. Call OpenAI Responses API
        // -------------------------------

        // $response = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        //     'Content-Type' => 'application/json',
        // ])->post('https://api.openai.com/v1/responses', [
        //     'model' => 'gpt-4o',
        //     'input' => [
        //         ['role' => 'system', 'content' => 'You generate structured fitness & nutrition plans.'],
        //         ['role' => 'user', 'content' => $prompt],
        //     ],
        //     'max_output_tokens' => 1200,
        // ]);


        // TEST PHASE: Use static test data instead of OpenAI API
        $response = new class {
            public function failed()
            {
                return false;
            }
            public function json()
            {
                return [
                    'output_text' => "Sample 4-week gym + nutrition plan:\n\nWeek 1: Full body workouts 3x/week, focus on form. Daily diet: lean protein, veggies, whole grains. Safety: Start slow, hydrate, rest.\n\nWeek 2: Increase intensity, add cardio. Diet: more fruits, balanced carbs. Safety: Listen to your body.\n\nWeek 3: Mix strength & HIIT. Diet: healthy fats, portion control. Safety: Stretch before/after workouts.\n\nWeek 4: Challenge yourself, track progress. Diet: maintain variety, avoid processed foods. Safety: Celebrate achievements, stay motivated!",
                ];
            }
        };

        if ($response->failed()) {
            \Log::error('OpenAI API failed', ['resp' => $response->body()]);
            return response()->json(['error' => 'Plan generation failed'], 500);
        }

        $json = $response->json();
        $planText = $json['output_text'] ?? ($json['output'][0]['content'][0]['text'] ?? '');

        // -------------------------------
        // 3. Generate PDF
        // -------------------------------
        $pdf = Pdf::loadView('pdf.plan', ['plan' => $planText, 'goal' => $goal]);
        $fileName = 'plans/' . uniqid('plan_') . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        // -------------------------------
        // 4. Save PDF path in another table
        // -------------------------------
        $planPdf = PlanPdf::create([
            'user_id' => $goal->user_id,
            'goal_id' => $goal->id,
            'file_path' => $fileName,
        ]);

        // -------------------------------
        // 5. Emails (your existing code)
        // -------------------------------

        // send email with attachment + link
        Mail::to($goal->email)->send(new UserThankYouMail($goal, $fileName));
        // admin email (without attachment if not needed)
        Mail::to(config('mail.admin_email'))->send(new AdminGoalNotification($goal));

        return response()->json([
            'message' => 'Goal saved, plan generated, and PDF created!',
            'goal' => $goal,
            'plan_pdf' => $planPdf,
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
