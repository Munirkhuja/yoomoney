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
            $table->string('id',50);
            $table->foreignId('yoo_money_id')->constrained('yoo_money')->onDelete('cascade');
            $table->enum('status',['waiting_for_capture','pending','succeeded','canceled'])->default('pending');
            $table->boolean('paid')->default(false);
            $table->text('description')->nullable();
            $table->text('metadata')->nullable();
            $table->json('authorization_details');
            $table->json('payment_method');
            $table->timestamp('expires_at')->default(\Carbon\Carbon::now());
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
        Schema::dropIfExists('yoo_money_change_statuses');
    }
};
