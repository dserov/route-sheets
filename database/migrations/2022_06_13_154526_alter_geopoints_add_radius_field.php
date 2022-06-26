<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGeopointsAddRadiusField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('geo_points', function (Blueprint $table) {
        $table->smallInteger('radius')->default(0)->comment('Радиус геозоны')->after('point');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('geo_points', function (Blueprint $table) {
        $table->dropColumn('radius');
      });
    }
}
