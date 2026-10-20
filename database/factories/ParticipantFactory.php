<?php

namespace Database\Factories;

use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Participant;
use App\Models\Patrol;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'patrol_id' => Patrol::factory(),
            'full_name' => $this->faker->name(),
            'scout_id' => '02.08.'.$this->faker->numerify('##.###'),
            'role_in_patrol' => PatrolRoleEnum::Member,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'birth_date' => $this->faker->dateTimeBetween('-15 years', '-12 years')->format('Y-m-d'),
            'birth_place' => 'Padangsidimpuan',
            'blood_type' => $this->faker->randomElement(['A', 'B', 'AB', 'O']),
            'qr_token' => 'PST-'.Str::upper(Str::random(10)),
            'status' => RegistrationStatusEnum::Draft,
        ];
    }
}
