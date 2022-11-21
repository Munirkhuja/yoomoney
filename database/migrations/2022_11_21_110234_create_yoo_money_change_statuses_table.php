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
        Schema::create('yoo_money_change_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('yoo_money_id',50);
            $table->enum('status',['waiting_for_capture','pending','succeeded','canceled'])->default('pending');
            $table->boolean('paid')->default(false);
            $table->json('authorization_details');
            $table->json('payment_method');
            $table->timestamp('expires_at');
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
        Schema::dropIfExists('yoo_money_change_statuses');
    }
};
