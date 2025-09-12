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
    public function __construct(Goal $goal, string $filePath = null)
    {
        $this->goal = $goal;
        $this->filePath = $filePath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $builder = $this->subject('Your Personalized Fitness & Nutrition Plan')
            ->view('emails.user.user_goal')
            ->with([
                'goal' => $this->goal,
                'downloadUrl' => $this->filePath ? Storage::disk('public')->url($this->filePath) : null,
            ]);

        if ($this->filePath) {
            $builder->attach(Storage::disk('public')->path($this->filePath), [
                'as' => 'fitness_plan.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $builder;
    }
}
