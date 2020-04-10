<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->increments('plan_id');
            $table->string('id');
            $table->integer('team_id');
            $table->integer('stripe_id');
            $table->enum('type', ['user', 'team'])->default('user');
            $table->string('name');
            $table->string('description');
            $table->float('price', 10, 2);
            $table->integer('trial_days')->default(0);
            $table->enum('interval', ['daily','weekly','monthly', 'yearly'])->default('monthly');
            $table->json('features')->nullable();
            $table->json('attributes')->nullable();
            $table->boolean('active')->default(true)->index();
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
        Schema::dropIfExists('plans');
    }
}
