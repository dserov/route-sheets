<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTableUts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('geo_point_id');
            $table->string('playground', 1024)->nullable();
            $table->string('container_type')->nullable();
            $table->string('container_volume')->nullable();
            $table->string('ut_number');
            $table->string('export_schedule')->nullable();
            $table->string('export_days')->nullable();
            $table->string('export_volume')->nullable();
            //
            $table->index('ut_number', 'idx_ut_number');
            $table->foreign('geo_point_id')
                ->references('id')
                ->on('geo_points')
                ->cascadeOnDelete()
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uts');
    }
}
