<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\Error;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class AuthService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function signup(array $userDetails): array
    {
        $existingUser = $this->manager->getRepository(User::class)->findOneBy(['login' => $userDetails['login']]);
        if ($existingUser) {
            throw new Error('User with this login already exists');
        }

        $user = new User($userDetails['login'], $userDetails['password'], $userDetails['name'] ?? null);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $userDetails['password']);
        $user->setPassword($hashedPassword);

        $this->manager->persist($user);
        $this->manager->flush();

        return [
            'token' => $this->jwtManager->create($user),
            'user' => $user,
        ];
    }

    public function login(array $credentials): array
    {
        $user = $this->manager->getRepository(User::class)->findOneBy(['login' => $credentials['login']]);
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $credentials['password'])) {
            throw new Error('Invalid credentials');
        }

        return [
            'token' => $this->jwtManager->create($user),
            'user' => $user,
        ];
    }
}