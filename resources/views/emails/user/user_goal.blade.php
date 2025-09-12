<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Fitness Plan</title>
</head>
<body style="background-color:rgb(55,65,81); font-family:'Segoe UI', Tahoma, sans-serif; margin:0; padding:40px 0;">
    <div style="max-width:650px; margin:0 auto; background-color:#1f2937; padding:30px; border-radius:12px; color:#f9fafb; box-shadow:0 4px 20px rgba(0,0,0,0.3);">

        <!-- Website Logo / Name -->
        <div style="text-align:center; margin-bottom:20px;">
            <a href="{{ url('/') }}" style="color:rgb(220,38,38); font-size:28px; font-weight:800; text-decoration:none;">
                AJURNIE
            </a>
        </div>

        <!-- Title -->
        <h2 style="color:rgb(220,38,38); text-align:center; margin-bottom:10px;">
            Thank You, {{ $goal->name }}!
        </h2>

        <!-- Intro -->
        <p style="text-align:center; color:#d1d5db; font-size:15px; margin-bottom:25px;">
            We’ve generated your personalized <strong>Fitness & Nutrition Plan</strong>. 🎉<br>
            You can <strong>download it as a PDF (attached to this email)</strong> or <strong>view it online</strong> using the button below.
        </p>

        <!-- Button to view PDF -->
        <div style="text-align:center; margin:25px 0;">
            <a href="{{ $downloadUrl }}" style="background-color:rgb(220,38,38); color:white; padding:12px 22px; border-radius:6px; font-weight:600; text-decoration:none; display:inline-block;" target="_blank">
                View Plan in Browser
            </a>
        </div>

        <!-- User Information Recap -->
        <h3 style="color:#f9fafb; font-size:18px; margin-bottom:12px;">Your Submitted Information</h3>
        <table style="width:100%; border-collapse:collapse; font-size:14px; margin-top:10px; margin-bottom:20px;">
            <thead>
                <tr>
                    <th style="background-color:rgb(220,38,38); color:white; text-align:left; padding:10px;">Field</th>
                    <th style="background-color:rgb(220,38,38); color:white; text-align:left; padding:10px;">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr><td style="padding:10px; border-top:1px solid #374151;">Name</td><td style="padding:10px; border-top:1px solid #374151;">{{ $goal->name }}</td></tr>
                <tr style="background-color:#111827;"><td style="padding:10px;">Email</td><td style="padding:10px;">{{ $goal->email }}</td></tr>
                <tr><td style="padding:10px;">Gender</td><td style="padding:10px;">{{ $goal->gender }}</td></tr>
                <tr style="background-color:#111827;"><td style="padding:10px;">Age</td><td style="padding:10px;">{{ $goal->age }}</td></tr>
                <tr><td style="padding:10px;">Height</td><td style="padding:10px;">{{ $goal->height }} cm</td></tr>
                <tr style="background-color:#111827;"><td style="padding:10px;">Current Weight</td><td style="padding:10px;">{{ $goal->current_weight }} kg</td></tr>
                <tr><td style="padding:10px;">Fitness Goal</td><td style="padding:10px;">{{ $goal->fitness_goal }}</td></tr>
                <tr style="background-color:#111827;"><td style="padding:10px;">Target Weight</td><td style="padding:10px;">{{ $goal->target_weight }} kg</td></tr>
                <tr><td style="padding:10px;">Deadline</td><td style="padding:10px;">{{ $goal->deadline }}</td></tr>
                <tr style="background-color:#111827;"><td style="padding:10px;">Activity Level</td><td style="padding:10px;">{{ $goal->activity_level }}</td></tr>
                <tr><td style="padding:10px;">Workout Style</td><td style="padding:10px;">{{ $goal->workout_style }}</td></tr>
                <tr style="background-color:#111827;"><td style="padding:10px;">Medical Conditions</td><td style="padding:10px;">{{ $goal->medical_conditions }}</td></tr>
                <tr><td style="padding:10px;">Dietary Preferences</td><td style="padding:10px;">{{ implode(', ', $goal->dietary_preferences ?? []) }}</td></tr>
                <tr style="background-color:#111827;"><td style="padding:10px;">Food Allergies</td><td style="padding:10px;">{{ $goal->food_allergies }}</td></tr>
                <tr><td style="padding:10px;">Plan Generated</td><td style="padding:10px;">{{ $goal->plan_generated ? 'Yes' : 'No' }}</td></tr>
            </tbody>
        </table>

        <!-- Footer -->
        <p style="text-align:center; color:#9ca3af; font-size:13px; margin-top:25px;">
            Thanks,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
