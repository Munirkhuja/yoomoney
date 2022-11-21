<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('yoo_money', function (Blueprint $table) {
            $table->id();
            $table->string('yoo_money_id',50)->default('');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount',15,2);
            $table->enum('currency',['RUB','USD'])->default('RUB');
            $table->text('description')->nullable();
            $table->text('metadata')->nullable();
            $table->boolean('paid')->default(false);
            $table->enum('status',['waiting_for_capture','pending','succeeded','canceled'])->default('pending');
            $table->string('recipient_account_id',50)->nullable();
            $table->string('recipient_gateway_id',50)->nullable();
            $table->boolean('refundable')->default(false);
            $table->boolean('test')->default(false);
            $table->timestamp('yoo_created_at')->default(\Carbon\Carbon::now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yoo_money');
    }
};
