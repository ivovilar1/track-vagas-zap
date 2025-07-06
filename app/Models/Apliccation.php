<?php

namespace App\Models;

use App\Enums\ApplicationStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apliccation extends Model
{
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'status' => ApplicationStatusEnum::class,
    ];
}
