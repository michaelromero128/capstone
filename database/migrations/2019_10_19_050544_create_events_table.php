<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('event_title');
            $table->text('event_description');
            $table->text('host_organization');
            $table->text('event_coordinator_name');
            $table->text('event_coordinator_phone',100);
            $table->text('event_coordinator_email');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('start_time');
            $table->text('end_time');
            $table->string('requirements_major',255);
            $table->string('requirements_year',255);
            $table->text('requirement_one');
            $table->text('requirement_two');
            $table->text('requirement_three');
            $table->integer('age_requirement');
            $table->integer('minimum_hours');
            $table->text('tags');
            $table->string('category', 30);
            $table->text('shifts');
            $table->string('city',255);
            $table->string('address',255);
            $table->integer('zipcode');
            $table->decimal('lat',8,5);
            $table->decimal('lon',8,5);
            $table->bigInteger('user_id')->unsigned()->nullable(true);
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
            
        });

        DB::statement('ALTER TABLE events ADD FULLTEXT fulltext_index (tags, event_description, host_organization, event_title, requirements_major, requirements_year, category)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
