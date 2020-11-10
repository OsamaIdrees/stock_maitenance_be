<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('product_sale',function(Blueprint $table){
            $table->increments('id');
            $table->integer('p_id')->unsigned()->nullable();
            $table->foreign('p_id')->references('id')->on('product')->onDelete('cascade');
            $table->integer('sell_record');
            $table->integer('revenue_earned');
            $table->integer('profit_earned');
            $table->timestamp('last_updated');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
