<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // This is critical
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'branch_id'];

    // Add these fields that Laravel authentication expects
    protected $hidden = ['password', 'remember_token'];

    // Keep your existing relationship
    public function branch() {
        return $this->belongsTo(Branch::class);
    }
}
