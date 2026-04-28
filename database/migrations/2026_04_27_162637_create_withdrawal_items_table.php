<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawal_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('withdrawal_id')
                ->constrained('withdrawals')
                ->cascadeOnDelete();

            $table->foreignId('denomination_id')
                ->constrained('denominations')
                ->cascadeOnDelete();

            $table->unsignedInteger('quantity');

            $table->timestamps();

            $table->unique(['withdrawal_id', 'denomination_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdrawal_items');
    }
}
