<?php

namespace Database\Seeders;

use App\Models\SheetDetail;
use Illuminate\Database\Seeder;

class SheetDetailTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SheetDetail::factory(100)->create();
    }
}
