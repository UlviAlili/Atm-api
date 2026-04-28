<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtmCashBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atm_cash_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('denomination_id')
                ->constrained('denominations')
                ->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(0);

            $table->timestamps();

            $table->unique('denomination_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('atm_cash_balances');
    }
}
