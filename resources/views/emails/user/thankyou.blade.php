<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thank You</title>
</head>
<body style="background-color:rgb(55,65,81); font-family:'Segoe UI', Tahoma, sans-serif; margin:0; padding:40px 0;">
    <div style="max-width:650px; margin:0 auto; background-color:#1f2937; padding:30px; border-radius:12px; color:#f9fafb; box-shadow:0 4px 20px rgba(0,0,0,0.3);">

        <div style="text-align:center; margin-bottom:20px;">
            <a href="{{ url('/') }}" style="color:rgb(220,38,38); font-size:28px; font-weight:800; text-decoration:none;">
                AJURNIE
            </a>
        </div>

        <h2 style="color:rgb(220,38,38); text-align:center; margin-bottom:10px;">
            Thank You, {{ $goal->name }}!
        </h2>

        <p style="text-align:center; color:#d1d5db; font-size:15px; margin-bottom:25px;">
            We’ve received your details and will prepare your personalized diet plan shortly.
        </p>

        <table style="width:100%; border-collapse:collapse; font-size:14px; margin-bottom:20px;">
            <thead>
                <tr>
                    <th style="background-color:rgb(220,38,38); color:white; text-align:left; padding:10px;">Field</th>
                    <th style="background-color:rgb(220,38,38); color:white; text-align:left; padding:10px;">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:10px; border-top:1px solid #374151;">Your Goal</td>
                    <td style="padding:10px; border-top:1px solid #374151;">{{ ucfirst($goal->fitness_goal) }}</td>
                </tr>
                <tr style="background-color:#111827;">
                    <td style="padding:10px;">Target Weight</td>
                    <td style="padding:10px;">{{ $goal->target_weight }} kg</td>
                </tr>
            </tbody>
        </table>

        <div style="text-align:center; margin-top:20px;">
            <a href="{{url('/')}}" style="background-color:rgb(220,38,38); color:white; padding:12px 20px; border-radius:6px; font-weight:600; text-decoration:none; display:inline-block;">
                Visit Our Website
            </a>
        </div>

        <p style="text-align:center; color:#9ca3af; font-size:13px; margin-top:25px;">
            Thanks,<br>
            {{ config('app.name') }}
        </p>

    </div>
</body>
</html>
