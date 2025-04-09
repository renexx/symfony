<?php

namespace App\Tests;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectMutationServiceTest extends WebTestCase
{
    public function testCreateProject(): void
    {
        $client = static::createClient();
        self::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $user = new User('test-login', 'test-password', 'Test User');
        $em->persist($user);
        $em->flush();

        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);

        $token = $jwtManager->create($user);

        // GraphQL mutation
        $mutation = <<<'GRAPHQL'
        mutation {
          createProject(input: { name: "New Project", description: "Test Description" }) {
            id
            name
            description
          }
        }
        GRAPHQL;

        $client->request(
            'POST',
            '/api/graphql/',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_Authorization' => 'Bearer ' . $token,
            ],
            json_encode(['query' => $mutation])
        );

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('New Project', $response['data']['createProject']['name']);
        $this->assertEquals('Test Description', $response['data']['createProject']['description']);
    }

    public function testUpdateProject(): void
    {
        $client = static::createClient();
        self::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $user = new User('test-login', 'test-password', 'Test User');
        $em->persist($user);

        $project = new Project('Old Project', $user, 'Old Description');
        $user->addProject($project);
        $em->persist($project);
        $em->flush();

        $this->assertEquals($user->getId(), $project->getOwner()->getId());

        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $mutation = <<<'GRAPHQL'
    mutation {
      updateProject(id: "%s", input: { name: "Updated Project", description: "Updated Description" }) {
        id
        name
        description
      }
    }
    GRAPHQL;

        $client->request(
            'POST',
            '/api/graphql/',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_Authorization' => 'Bearer ' . $token,
            ],
            json_encode(['query' => sprintf($mutation, $project->getId())])
        );

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Updated Project', $response['data']['updateProject']['name']);
        $this->assertEquals('Updated Description', $response['data']['updateProject']['description']);
    }
}