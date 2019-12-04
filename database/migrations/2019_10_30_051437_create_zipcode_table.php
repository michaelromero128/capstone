<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZipcodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zipcodes', function (Blueprint $table) {
            $table->integer('zipcode');
            $table->decimal('lon',8,5);
            $table->decimal('lat',8,5);
            $table->primary('zipcode');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zipcodes');
    }
}
