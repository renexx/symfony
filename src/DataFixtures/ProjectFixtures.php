<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 100; $i++) {
            $project = $this->getFakeProject($i % 10);
            $project->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($project);
        }
        $manager->flush();
    }

    private function getFakeProject(int $userIndex): Project
    {
        return new Project(
            $this->faker->sentence(),
            $this->getReference(UserFixtures::REFERENCE_PREFIX . $userIndex, User::class),
            $this->faker->sentence()
        );
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
