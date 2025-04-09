<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class UserFixtures extends Fixture
{
    public const REFERENCE_PREFIX = 'USER_REFERENCE_';
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $user = $this->getFakeUser();
            $this->addReference(self::REFERENCE_PREFIX . $i, $user);
            $manager->persist($user);
        }

        $manager->flush();
    }
    private function getFakeUser(): User
    {
        return new User(
            $this->faker->userName(),
            $this->faker->password(),
            $this->faker->name()
        );
    }
}
