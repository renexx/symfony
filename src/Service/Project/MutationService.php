<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\Error;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\SecurityBundle\Security;


readonly class MutationService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private Security $security,
        private ProjectRepository $projectRepository
    ) {
    }

    public function updateProject(Uuid|string $id, array $input): Project
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new Error('Not authenticated');
        }

        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw new Error('Project not found');
        }

        if ($project->getOwner()->getId() !== $user->getId()) {
            throw new Error('Not authorized to update this project');
        }

        if (isset($input['name'])) {
            $project->setName($input['name']);
        }
        if (isset($input['description'])) {
            $project->setDescription($input['description']);
        }

        $project->setUpdatedAt(new DateTime());
        $this->manager->flush();

        return $project;
    }

    public function createProject(array $input): Project
    {
        $user = $this->security->getUser();
        if (!$user) {
            $token = $this->security->getToken();
            $tokenInfo = $token ? get_class($token) : 'Token neexistuje';
            throw new Error('Not authenticated. Token info: ' . $tokenInfo);
        }

        $project = new Project($input['name'], $user, $input['description'] ?? null);
        $this->manager->persist($project);
        $this->manager->flush();

        return $project;
    }

    /**
     * @throws Error
     */
    public function deleteProject(Uuid|string $id): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new Error('Not authenticated');
        }

        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw new Error('Project not found');
        }

        if ($project->getOwner()->getId() !== $user->getId()) {
            throw new Error('Not authorized to delete this project');
        }

        $projectId = $project->getId();
        $this->manager->remove($project);
        $this->manager->flush();

        return [
            'id' => $projectId,
            'success' => true,
        ];
    }
}