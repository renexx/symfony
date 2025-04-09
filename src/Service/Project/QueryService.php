<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\Error;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Uuid;

readonly class QueryService
{
    public function __construct(
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private Security $security,

    ) {
    }

    public function findUser(Uuid|string $userId): ?User
    {
        return $this->userRepository->find($userId);
    }

    public function findAllProjects(): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new Error('Not authenticated');
        }

        return $this->projectRepository->findBy(['owner' => $user->getId()]);
    }

    public function findProjectById(Uuid|string $projectId): ?Project
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new Error('Not authenticated');
        }

        $project = $this->projectRepository->find($projectId);
        if (!$project) {
            throw new Error('Project not found');
        }

        if ($project->getOwner()->getId() !== $user->getId()) {
            throw new Error('Not authorized to access this project');
        }

        return $project;
    }
}