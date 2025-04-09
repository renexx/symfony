<?php

namespace App\Resolver;

use App\Service\Project\MutationService as ProjectMutationService;
use App\Service\User\MutationService as UserMutationService;
use App\Service\Project\QueryService as ProjectQueryService;
use App\Service\User\QueryService as UserQueryService;
use App\Service\AuthService;
use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class CustomResolverMap extends ResolverMap
{
    public function __construct(
        private readonly UserQueryService $userQueryService,
        private readonly ProjectQueryService $projectQueryService,
        private readonly UserMutationService $userMutationService,
        private readonly ProjectMutationService $projectMutationService,
        private readonly AuthService $authService
    ) {
    }

    protected function map(): array
    {
        return [
            'RootQuery' => [
                self::RESOLVE_FIELD => function (
                    $value,
                    ArgumentInterface $args,
                    ArrayObject $context,
                    ResolveInfo $info
                ) {
                    return match ($info->fieldName) {
                        'projects' => $this->projectQueryService->findAllProjects(),
                        'project' => $this->projectQueryService->findProjectById($args['id']),
                        'me' => $this->userQueryService->me(),
                        default => null
                    };
                },
            ],
            'RootMutation' => [
                self::RESOLVE_FIELD => function (
                    $value,
                    ArgumentInterface $args,
                    ArrayObject $context,
                    ResolveInfo $info
                ) {
                    return match ($info->fieldName) {
                        'updateUser' => $this->userMutationService->updateUser($args['input']),
                        'signup' => $this->authService->signup($args['input']),
                        'login' => $this->authService->login($args['input']),
                        'updateProject' => $this->projectMutationService->updateProject($args['id'], $args['input']),
                        'createProject' => $this->projectMutationService->createProject($args['input']),
                        'deleteProject' => $this->projectMutationService->deleteProject($args['id']),
                        default => null
                    };
                },
            ],
        ];
    }
}