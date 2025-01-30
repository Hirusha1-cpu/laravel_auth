<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

        public function up()
        {
            Schema::table('users', function (Blueprint $table) {
                $table->date('joinned_date')->nullable();
                $table->integer('leave_count')->default(0);
                $table->string('finger_printid')->nullable();
            });
        }
    
        public function down()
        {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['joinned_date', 'leave_count', 'finger_printid']);
            });
        }
};
