<?php

namespace App\Service\User;

use App\Entity\User;
use GraphQL\Error\Error;
use Symfony\Bundle\SecurityBundle\Security;

readonly class QueryService
{
    public function __construct(
        private Security $security
    ) {}

    public function me(): User
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new Error('Not authenticated');
        }

        return $user;
    }
}