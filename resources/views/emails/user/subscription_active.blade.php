@component('mail::message')
# Welcome, {{ $user->fullname }} 🎉

Thank you for joining **Our Fitness Platform**.
You now have access to personalized plans and tools.

@component('mail::button', ['url' => config('app.url')])
Go to Dashboard
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponen
