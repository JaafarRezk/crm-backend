<?php
// database/migrations/2025_11_09_000000_create_communications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();

            // foreign id without automatic constraint name so we create explicit foreign() to allow naming
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('created_by')->index();

            $table->enum('type', ['call','email','meeting'])->index();
            $table->timestamp('date')->useCurrent()->index();
            $table->text('notes')->nullable();

            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at (for restore support)

            // composite index to speed queries by client + date
            $table->index(['client_id','date']);

            // explicit foreign key constraints with names
            $table->foreign('client_id', 'communications_client_id_fk')
                  ->references('id')->on('clients')
                  ->onDelete('cascade');

            $table->foreign('created_by', 'communications_created_by_fk')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('communications', function (Blueprint $table) {
            // drop foreign keys first (by name)
            if (Schema::hasColumn('communications', 'client_id')) {
                $table->dropForeign('communications_client_id_fk');
            }
            if (Schema::hasColumn('communications', 'created_by')) {
                $table->dropForeign('communications_created_by_fk');
            }
        });

        Schema::dropIfExists('communications');
    }
};
