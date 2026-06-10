<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('ticket_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                  ->constrained('tickets')
                  ->onDelete('cascade');

            $table->foreignId('changed_by')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('field');   // 'status', 'priority', 'assigned_to', etc.
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_histories');
    }
}
