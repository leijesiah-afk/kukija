<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_user_id',
        'action_type',
        'description',
        'ip_address',
    ];

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
