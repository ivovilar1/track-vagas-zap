<?php

namespace App\Models;

use App\Enums\ApplicationStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'job_title',
        'job_description',
        'job_salary',
        'job_link',
        'status',
        'application_date',
        'application_date_end',
    ];

    protected $casts = [
        'status' => ApplicationStatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
