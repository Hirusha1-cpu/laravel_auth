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
            $table->enum('leave_type', ['annual', 'casual'])->default('annual')->after('end_date');

        });

        // Update existing records
        DB::table('leave_request')->update([
            'start_date' => DB::raw('`date`'),
            'end_date' => DB::raw('`date`'),
            'leave_type' => 'annual'
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
        Schema::table('leave_request', function (Blueprint $table) {
            $table->date('date')->after('id');
            
            // Update the date column with start_date values
            DB::table('leave_request')->update([
                'date' => DB::raw('`start_date`')
            ]);

            $table->dropColumn(['start_date', 'end_date', 'leave_type']);
        });

        Schema::dropIfExists('leave_request_managers');
    }
};