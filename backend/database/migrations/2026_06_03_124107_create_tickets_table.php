<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {

            $table->id();

            $table->string('title');

            $table->text('description');

            $table->enum('status', [
                'OPEN',
                'IN_PROGRESS',
                'CLOSED'
            ])->default('OPEN');

            $table->enum('priority', [
                'LOW',
                'MEDIUM',
                'HIGH',
                'CRITICAL'
            ])->default('LOW');

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
