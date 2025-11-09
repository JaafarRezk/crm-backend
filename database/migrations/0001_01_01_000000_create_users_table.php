<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            // roles used by system users (not clients)
            $table->enum('user_type', ['admin','manager','sales_rep'])->default('sales_rep')->index();
            // security fields for brute-force protection
            $table->unsignedTinyInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable()->index();
            // track last successful login
            $table->timestamp('last_login')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // performance indices
            $table->index(['email']); // already unique, but explicit for clarity
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
