<?php

namespace Database\Seeders;

use App\Models\Sheet;
use Illuminate\Database\Seeder;

class SheetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Sheet::factory(10)->create();
    }
}
