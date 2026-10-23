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
        $maleNum = rand(1, 499) * 2 - 1;
        $femaleNum = $maleNum + 1;
        $groupNum = sprintf('01-%03d & 01-%03d', $maleNum, $femaleNum);

        return [
            'user_id' => User::factory(),
            'registration_code' => ScoutGroup::generateGudepId($groupNum),
            'school_name' => 'SMP Negeri '.$this->faker->numberBetween(1, 15).' Padangsidimpuan',
            'group_number' => $groupNum,
            'male_group_number' => sprintf('01-%03d', $maleNum),
            'female_group_number' => sprintf('01-%03d', $femaleNum),
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
