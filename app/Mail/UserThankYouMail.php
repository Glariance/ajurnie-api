<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Goal;
use Illuminate\Support\Facades\Storage;

class UserThankYouMail extends Mailable
{
    use Queueable, SerializesModels;

    public $goal;
    public $filePath;

    /**
     * Create a new message instance.
     */
    public function __construct(Goal $goal, string $filePath)
    {
        $this->goal = $goal;
        $this->filePath = $filePath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Personalized Fitness & Nutrition Plan')
            ->view('emails.user.user_goal') // your custom HTML view
            ->with([
                'goal' => $this->goal,
                'downloadUrl' => Storage::url($this->filePath),
            ])
            ->attach(Storage::path($this->filePath), [
                'as' => 'fitness_plan.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
