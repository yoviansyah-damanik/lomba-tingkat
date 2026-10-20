<?php

namespace Database\Factories;

use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\ScoutGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Chaperone>
 */
class ChaperoneFactory extends Factory
{
    protected $model = Chaperone::class;

    public function definition(): array
    {
        return [
            'scout_group_id' => ScoutGroup::factory(),
            'full_name' => $this->faker->name().', S.Pd',
            'scout_id' => '02.08.'.$this->faker->numerify('##.9##'),
            'national_identity_number' => '1277'.$this->faker->numerify('############'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'phone_number' => '08'.$this->faker->numerify('##########'),
            'qr_token' => 'BND-'.Str::upper(Str::random(10)),
            'status' => RegistrationStatusEnum::Draft,
        ];
    }
}
