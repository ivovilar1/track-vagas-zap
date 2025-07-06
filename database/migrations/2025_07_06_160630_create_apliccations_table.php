<?php

use App\Enums\ApplicationStatusEnum;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apliccations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('company_name')->nullable();
            $table->string('job_title');
            $table->string('job_description')->nullable();
            $table->string('job_salary')->nullable();
            $table->string('job_link')->nullable();
            $table->string('status')->default(ApplicationStatusEnum::PENDING->value);
            $table->date('application_date');
            $table->date('application_date_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apliccations');
    }
};
