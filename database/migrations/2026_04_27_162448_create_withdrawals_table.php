<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('cascade');

            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->decimal('amount', 15, 2);

            $table->enum('status', ['success', 'failed'])->default('success');

            $table->string('failure_reason')->nullable();

            $table->string('idempotency_key')->nullable()->unique();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdrawals');
    }
}
