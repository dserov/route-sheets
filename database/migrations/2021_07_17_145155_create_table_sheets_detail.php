<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSheetsDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheet_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sheet_id');
            $table->unsignedInteger('npp')->comment('номер пп')->nullable();
            $table->string('contragent')->comment('контрагент')->nullable();
            $table->string('playground')->comment('площадка')->nullable();
            $table->string('overflow')->comment('переполнение')->nullable();
            $table->string('note')->comment('примечание')->nullable();
            $table->float('volume')->comment('объем контрагента')->nullable();
            $table->integer('count_plan')->comment('количество план')->nullable();
            $table->float('count_units')->comment('количество единицы')->nullable();
            $table->float('count_fact')->comment('количество факт')->nullable();
            $table->float('count_general')->comment('количество общии')->nullable();
            $table->string('mark')->comment('отметка')->nullable();
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
        Schema::dropIfExists('sheet_details');
    }
}
