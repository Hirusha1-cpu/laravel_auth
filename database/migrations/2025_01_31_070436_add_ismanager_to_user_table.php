<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the assigned_manager column
            $table->unsignedBigInteger('assigned_manager')->nullable()->after('half_day_count');

            // Add the account_status column
            $table->enum('account_status', ['Pending', 'Approved', 'Rejected'])->default('Pending')->after('assigned_manager');

            // Add foreign key constraint for assigned_manager if you want it to refer to the users table
            $table->foreign('assigned_manager')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the columns if rolling back
            $table->dropForeign(['assigned_manager']);
            $table->dropColumn(['assigned_manager', 'account_status']);
        });
    }
};
