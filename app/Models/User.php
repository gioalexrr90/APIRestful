<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const VERIFIED = false;

    const ADMINISTRATOR = false;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'verified',
        'verification_token',
        'admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    public function is_verified()
    {
        return $this->verified == User::VERIFIED;
    }

    public function is_administrator()
    {
        return $this->administrator == User::ADMINISTRATOR;
    }

    public static function generate_token_verification()
    {
        return Str::random(40);
    }
}
