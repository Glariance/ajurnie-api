<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    //
    protected $fillable = [
        
        'user_id',
        'name',
        'email',
        'gender',
        'age',
        'height',
        'current_weight',
        'fitness_goal',
        'target_weight',
        'deadline',
        'activity_level',
        'workout_style',
        'medical_conditions',
        'dietary_preferences',
        'food_allergies',
        'plan_generated'

    ];


    protected $casts = [
        'dietary_preferences' => 'array',
    ];

}
