<?php

namespace Database\Factories;

use App\Enums\RegistrationStatusEnum;
use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ScoutGroup>
 */
class ScoutGroupFactory extends Factory
{
    protected $model = ScoutGroup::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'registration_code' => 'GDP-'.strtoupper(Str::random(6)),
            'school_name' => 'SMP Negeri '.$this->faker->numberBetween(1, 15).' Padangsidimpuan',
            'group_number' => sprintf('%02d.%03d - %02d.%03d', rand(1, 10), rand(1, 99), rand(1, 10), rand(1, 99)),
            'address' => $this->faker->streetAddress().', Kota Padangsidimpuan',
            'contact_person' => $this->faker->name(),
            'contact_phone' => '08'.rand(111111111, 999999999),
            'verification_status' => RegistrationStatusEnum::Draft,
            'revision_notes' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }
}
