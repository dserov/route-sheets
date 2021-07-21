<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailFoto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sheet_detail_id')->comment('код строки маршрутного листа');
            $table->string('name')->comment('имя файла')->nullable();
            $table->string('description')->comment('описание')->nullable();
            $table->string('path')->comment('относительный путь')->nullable();
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
        Schema::dropIfExists('detail_fotos');
    }
}
