<?php

namespace App\Tests\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Enum\UserRole;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();

        $this->createTestData();
    }

    private function createTestData(): void
    {
        $this->user = new User();
        $this->user->setName('User');
        $this->user->setEmail('user@example.com');
        $this->user->setPassword(password_hash('userpass', PASSWORD_BCRYPT));
        $this->user->setRole(UserRole::USER);
        $this->user->setIsVerified(true);
        $this->em->persist($this->user);

        $this->em->flush();
    }

    public function testCreateOrderWithCursus(): void
    {
        $cursus = new Cursus();
        $cursus->setName('Test Cursus');
        $cursus->setPrice(99.99);
        $this->em->persist($cursus);
        $this->em->flush();

        $this->client->loginUser($this->user);

        $payload = [
            'amount' => 99.99,
            'cursusId' => $cursus->getId()
        ];

        $this->client->request(
            'POST',
            '/api/create-order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $order = $this->em->getRepository(Order::class)->find($responseData['orderId']);
        
        $this->assertNotNull($order);
        $this->assertCount(1, $order->getOrderItems());
        $this->assertEquals($cursus->getId(), $order->getOrderItems()->first()->getCursus()->getId());
    }

    public function testCreateOrderWithLesson(): void
    {
        $cursus = new Cursus();
        $cursus->setName('Test Cursus');
        $cursus->setPrice(99.99);
        $this->em->persist($cursus);

        $lesson = new Lesson();
        $lesson->setName('Test Lesson');
        $lesson->setPrice(29.99);
        $lesson->setDescription('Test Description');
        $lesson->setVideoName('video.mp4');
        $lesson->setCursus($cursus);
        $this->em->persist($lesson);
        $this->em->flush();

        $this->client->loginUser($this->user);

        $payload = [
            'amount' => 29.99,
            'lessonId' => $lesson->getId()
        ];

        $this->client->request(
            'POST',
            '/api/create-order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $order = $this->em->getRepository(Order::class)->find($responseData['orderId']);
        
        $this->assertNotNull($order);
        $this->assertCount(1, $order->getOrderItems());
        $this->assertEquals($lesson->getId(), $order->getOrderItems()->first()->getLesson()->getId());
    }
}
