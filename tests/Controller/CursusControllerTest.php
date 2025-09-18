<?php

namespace App\Tests\Controller;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CursusControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;
    private User $admin;
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
        $this->admin = new User();
        $this->admin->setName('Admin');
        $this->admin->setEmail('admin@example.com');
        $this->admin->setPassword(password_hash('adminpass', PASSWORD_BCRYPT));
        $this->admin->setRole(UserRole::ADMIN);
        $this->admin->setIsVerified(true);
        $this->em->persist($this->admin);

        $this->user = new User();
        $this->user->setName('User');
        $this->user->setEmail('user@example.com');
        $this->user->setPassword(password_hash('userpass', PASSWORD_BCRYPT));
        $this->user->setRole(UserRole::USER);
        $this->user->setIsVerified(true);
        $this->em->persist($this->user);

        $this->em->flush();
    }

    public function testAdminCanCreateCursus(): void
    {
        $this->client->loginUser($this->admin);

        $payload = [
            'name' => 'Cursus PHP Avancé',
            'price' => 200,
        ];

        $this->client->request(
            'POST',
            '/api/cursus',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Cursus PHP Avancé', $responseData['name']);
        $this->assertEquals(200, $responseData['price']);

        $cursus = $this->em->getRepository(Cursus::class)->findOneBy(['name' => 'Cursus PHP Avancé']);
        $this->assertNotNull($cursus);
    }

    public function testNonAdminCannotCreateCursus(): void
    {
        $this->client->loginUser($this->user);

        $payload = [
            'name' => 'Cursus PHP Avancé',
            'price' => 199.99,
        ];

        $this->client->request(
            'POST',
            '/api/cursus',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }


    public function testGetCursusList(): void
{
    $cursus1 = new Cursus();
    $cursus1->setName('Cursus 1');
    $cursus1->setPrice(99.99);
    $this->em->persist($cursus1);

    $cursus2 = new Cursus();
    $cursus2->setName('Cursus 2');
    $cursus2->setPrice(149.99);
    $this->em->persist($cursus2);

    $lesson1 = new Lesson();
    $lesson1->setName('Leçon 1');
    $lesson1->setDescription('Description 1');
    $lesson1->setVideoUrl('https://example.com/video1.mp4');
    $lesson1->setPrice(25.25);
    $lesson1->setCursus($cursus1);
    $this->em->persist($lesson1);

    $lesson2 = new Lesson();
    $lesson2->setName('Leçon 2');
    $lesson2->setDescription('Description 2');
    $lesson2->setVideoUrl('https://example.com/video2.mp4');
    $lesson2->setPrice(25.25);
    $lesson2->setCursus($cursus2);
    $this->em->persist($lesson2);

    $cursus1->addLesson($lesson1);
    $cursus2->addLesson($lesson2);

    $this->em->flush();

    $this->client->request('GET', '/api/cursus?_format=jsonld', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
    $response = $this->client->getResponse();

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    $responseData = json_decode($response->getContent(), true);

    $this->assertArrayHasKey('member', $responseData);
    $this->assertCount(2, $responseData['member']);

    foreach ($responseData['member'] as $cursusData) {
        $this->assertArrayHasKey('lessons', $cursusData);
        $this->assertNotEmpty($cursusData['lessons']);
    }
}


    public function testGetCursusById(): void
    {
        $cursus = new Cursus();
        $cursus->setName('Cursus Test');
        $cursus->setPrice(199.99);
        $this->em->persist($cursus);
        $this->em->flush();

        $this->client->request('GET', '/api/cursus/' . $cursus->getId());

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Cursus Test', $responseData['name']);
        $this->assertEquals(199.99, $responseData['price']);
    }


    public function testAdminCanUpdateCursus(): void
    {
        $cursus = new Cursus();
        $cursus->setName('Cursus Original');
        $cursus->setPrice(99.99);
        $this->em->persist($cursus);
        $this->em->flush();

        $this->client->loginUser($this->admin);

        $payload = [
            'name' => 'Cursus Modifié',
            'price' => 199.99,
        ];

        $this->client->request(
            'PUT',
            '/api/cursus/' . $cursus->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Cursus Modifié', $responseData['name']);
        $this->assertEquals(199.99, $responseData['price']);
    }


    public function testNonAdminCannotUpdateCursus(): void
    {
        $cursus = new Cursus();
        $cursus->setName('Cursus Original');
        $cursus->setPrice(99.99);
        $this->em->persist($cursus);
        $this->em->flush();

        $this->client->loginUser($this->user);

        $payload = [
            'name' => 'Cursus Modifié',
            'price' => 199.99
        ];

        $this->client->request(
            'PUT',
            '/api/cursus/' . $cursus->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testAdminCanDeleteCursus(): void
    {
        $cursus = new Cursus();
        $cursus->setName('Cursus à supprimer');
        $cursus->setPrice(99.99);
        $this->em->persist($cursus);
        $this->em->flush();

        $cursusId = $cursus->getId();

        $this->client->loginUser($this->admin);
        $this->client->request('DELETE', '/api/cursus/' . $cursusId);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $deletedCursus = $this->em->getRepository(Cursus::class)->find($cursusId);
        $this->assertNull($deletedCursus);
    }
}
