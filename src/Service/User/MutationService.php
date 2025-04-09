<?php

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\Error;
use Symfony\Bundle\SecurityBundle\Security;

readonly class MutationService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private Security $security,
    ) {
    }

    public function updateUser(array $input): User
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new Error('Not authenticated');
        }

        if (isset($input['name'])) {
            $user->setName($input['name']);
        }
        if (isset($input['password'])) {
            $hashedPassword = password_hash($input['password'], PASSWORD_BCRYPT);
            $user->setPassword($hashedPassword);
        }

        $this->manager->flush();

        return $user;
    }
}