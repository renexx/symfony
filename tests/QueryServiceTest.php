<?php

namespace App\Tests;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QueryServiceTest extends WebTestCase
{
    public function testMeQueryReturnsOnlyAuthenticatedUserProjects(): void
    {
        $client = static::createClient();
        self::bootKernel();

        $em = static::getContainer()->get(EntityManagerInterface::class);

        $user1 = new User('user1-login', 'user1-password', 'User One');
        $em->persist($user1);

        $project1 = new Project('User One Project', $user1, 'User One Project Description');
        $em->persist($project1);

        $user2 = new User('user2-login', 'user2-password', 'User Two');
        $em->persist($user2);

        $project2 = new Project('User Two Project', $user2, 'User Two Project Description');
        $em->persist($project2);

        $em->flush();

        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user1);

        $query = <<<'GRAPHQL'
        query {
          me {
            id
            login
            name
            projects {
              id
              name
              description
            }
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
            json_encode(['query' => $query])
        );

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('me', $response['data']);
        $this->assertEquals('user1-login', $response['data']['me']['login']);
        $this->assertEquals('User One', $response['data']['me']['name']);
        $this->assertCount(1, $response['data']['me']['projects']);
        $this->assertEquals('User One Project', $response['data']['me']['projects'][0]['name']);
        $this->assertEquals('User One Project Description', $response['data']['me']['projects'][0]['description']);
    }
}