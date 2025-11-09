<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('email')->nullable()->index();
            $table->string('phone', 20)->nullable()->index();
            $table->enum('status', ['New','Active','Hot','Inactive','Cold'])->default('New')->index();
            // assigned_to references users.id (sales rep)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->index();
            $table->timestamp('last_communication_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // composite index for common queries (filter by assigned_to + status)
            $table->index(['assigned_to','status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
