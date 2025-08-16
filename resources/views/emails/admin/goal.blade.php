@component('mail::message')

<!-- Website name -->
<div style="text-align:center; margin-bottom:20px;">
    <a href="{{ url('/') }}" style="color:rgb(220,38,38); font-size:28px; font-weight:800; text-decoration:none;">
        AJURNIE
    </a>
</div>

<h2 style="color:rgb(220,38,38); text-align:center; margin-bottom:10px;">New Goal Submission</h2>
<p style="text-align:center; color:#d1d5db; font-size:15px;">
    A new fitness goal has been submitted:
</p>

<!-- Styled table -->
<table style="width:100%; border-collapse:collapse; font-size:14px; margin-top:20px;">
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

@endcomponent
