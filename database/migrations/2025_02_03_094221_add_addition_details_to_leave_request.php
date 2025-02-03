<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, add new columns as nullable
        Schema::table('leave_request', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('id');
            $table->date('end_date')->nullable()->after('start_date');
            $table->enum('leave_type', ['full', 'half'])->default('full')->after('end_date');
        });

        // Update existing records
        DB::table('leave_request')->update([
            'start_date' => DB::raw('`date`'),
            'end_date' => DB::raw('`date`'),
            'leave_type' => 'full'
        ]);

        // Make columns required after data migration
        Schema::table('leave_request', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
        });

        // Create managers pivot table
        Schema::create('leave_request_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_request')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Finally, drop the old date column
        Schema::table('leave_request', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

    public function down(): void
    {
        // First add the date column
        Schema::table('leave_request', function (Blueprint $table) {
            $table->date('date')->nullable()->after('id');
        });

        // Copy data back if the columns exist
        if (Schema::hasColumn('leave_request', 'start_date')) {
            DB::table('leave_request')->update([
                'date' => DB::raw('`start_date`')
            ]);
        }

        // Drop the new columns
        Schema::table('leave_request', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'leave_type']);
        });

        // Drop the managers table
        Schema::dropIfExists('leave_request_managers');
    }
};