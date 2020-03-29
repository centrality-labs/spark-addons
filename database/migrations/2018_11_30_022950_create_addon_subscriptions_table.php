<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddonSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addon_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subscription_id');
            $table->string('owner_id');
            $table->string('owner_type');
            $table->string('addon_id')->nullable();
            $table->string('addon_type')->nullable();
            $table->timestamp('ends_at')->nullable();
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
        Schema::dropIfExists('addon_subscriptions');
    }
}
