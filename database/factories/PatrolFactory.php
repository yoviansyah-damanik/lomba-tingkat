<?php

namespace Database\Factories;

use App\Enums\PatrolGenderEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Patrol>
 */
class PatrolFactory extends Factory
{
    protected $model = Patrol::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement([PatrolGenderEnum::Boys, PatrolGenderEnum::Girls]);
        $name = $gender === PatrolGenderEnum::Boys
            ? $this->faker->randomElement(['Rajawali', 'Garuda', 'Elang', 'Singa', 'Harimau', 'Kancil'])
            : $this->faker->randomElement(['Melati', 'Mawar', 'Anggrek', 'Dahlia', 'Teratai', 'Tulip']);

        return [
            'scout_group_id' => ScoutGroup::factory(),
            'patrol_name' => $name,
            'gender_category' => $gender,
            'status' => RegistrationStatusEnum::Draft,
        ];
    }
}
