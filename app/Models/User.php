<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'department_id',
        'joinned_date',
        'leave_count',
        'finger_printid',
        'half_day_count',
        'assigned_manager',
        'account_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Roles::class);
    }

    public function hasRole($role)
    {
        return $this->role->slug === $role;
    }

    // Define relationship for manager
    public function manager()
    {
        return $this->belongsTo(User::class, 'assigned_manager');
    }

    // Define relationship for subordinates
    public function subordinates()
    {
        return $this->hasMany(User::class, 'assigned_manager');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
