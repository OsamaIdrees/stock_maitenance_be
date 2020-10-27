<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('product_info',function(Blueprint $table){
            $table->increments('id');
            $table->integer('p_id')->unsigned()->nullable();
            $table->foreign('p_id')->references('id')->on('product')->onDelete('cascade');
            $table->integer('stock');
            $table->timestamp('updated_at');
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
