<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete()->index();
            $table->string('resource_type'); // e.g., Client, Communication, FollowUp, User
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->string('action'); // created, updated, deleted, auto_status_change
            $table->json('changes')->nullable(); // diff or payload
            $table->timestamps();

            $table->index(['resource_type','resource_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
