<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPerDaySaleRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('product_per_day_sale',function(Blueprint $table){
            $table->increments('id');
            $table->integer('p_id')->unsigned()->nullable();
            $table->foreign('p_id')->references('id')->on('product')->onDelete('cascade');
            $table->integer('stock_sell');
            $table->integer('average_price');
            $table->timestamp('Date');

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
