<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $table = 'leave_request';

    protected $fillable = [
        'start_date',
        'end_date',
        'leave_type',
        'reason',
        'users_id',
        'mailed_status',
        'accept_status',
        'not_accept_reason',
        'updated_user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function updatedUser()
    {
        return $this->belongsTo(User::class, 'updated_user_id');
    }

    public function managers()
    {
        return $this->belongsToMany(User::class, 'leave_request_managers', 'leave_request_id', 'manager_id')
            ->withTimestamps();
    }
}