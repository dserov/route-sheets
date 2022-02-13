<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRotateToDetailPhotos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detail_fotos', function (Blueprint $table) {
            $table->smallInteger('rotate')->default(0)->comment('Поворот картинки');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detail_fotos', function (Blueprint $table) {
            $table->dropColumn('rotate');
        });
    }
}
