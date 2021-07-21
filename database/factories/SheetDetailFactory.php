<?php

namespace Database\Factories;

use App\Models\Sheet;
use App\Models\SheetDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class SheetDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SheetDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $sheet = $this->faker->randomElement(Sheet::all());
        return [
            'sheet_id' => $sheet->id,
            'npp' => $this->faker->randomNumber(2),
            'contragent' => $this->faker->text(20),
            'playground' => $this->faker->text(20),
        ];
    }
}
