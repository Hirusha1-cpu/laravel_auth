<?php

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
        Schema::create('leave_request', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->text('reason');
            $table->foreignId('users_id')->constrained()->onDelete('cascade');
            $table->boolean('mailed_status')->default(false);
            $table->enum('accept_status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('not_accept_reason')->nullable();
            $table->foreignId('updated_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request');
    }
};
