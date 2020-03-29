<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStripeItemIdToSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_subscriptions', function (Blueprint $table) {
            $table->string('stripe_item_id')->after('stripe_plan')->nullable();
            $table->string('addon_id')->after('stripe_item_id')->nullable();
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('stripe_item_id')->after('stripe_plan')->nullable();
            $table->string('addon_id')->after('stripe_item_id')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_subscriptions', function (Blueprint $table) {
            $table->dropColumn('addon_id');
            $table->dropColumn('stripe_item_id');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('addon_id');
            $table->dropColumn('stripe_item_id');
        });
    }
}
