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

class LessonControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;
    private User $admin;
    private User $user;
    private Cursus $cursus;

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
        $this->cursus = new Cursus();
        $this->cursus->setName('Cursus PHP');
        $this->cursus->setPrice(99.99);
        $this->em->persist($this->cursus);

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
    
    public function testGetLessonsList(): void
    {
        $lesson1 = new Lesson();
        $lesson1->setName('Lesson 1');
        $lesson1->setPrice(19.99);
        $lesson1->setDescription('Description 1');
        $lesson1->setVideoUrl('https://example.com/video1.mp4');
        $lesson1->setCursus($this->cursus);
        $this->em->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setName('Lesson 2');
        $lesson2->setPrice(29.99);
        $lesson2->setDescription('Description 2');
        $lesson2->setVideoUrl('https://example.com/video2.mp4');
        $lesson2->setCursus($this->cursus);
        $this->em->persist($lesson2);

        $this->em->flush();

        $this->client->request('GET', '/api/lesson?_format=jsonld', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('member', $responseData);
        $this->assertCount(2, $responseData['member']);
    }

    public function testGetLessonById(): void
    {
        $lesson = new Lesson();
        $lesson->setName('Lesson Test');
        $lesson->setPrice(19.99);
        $lesson->setDescription('Description test');
        $lesson->setVideoUrl('https://example.com/video.mp4');
        $lesson->setCursus($this->cursus);
        $this->em->persist($lesson);
        $this->em->flush();

        $this->client->request('GET', '/api/lesson/' . $lesson->getId());

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Lesson Test', $responseData['name']);
        $this->assertEquals(19.99, $responseData['price']);
        $this->assertArrayNotHasKey('description', $responseData);
        $this->assertArrayNotHasKey('videoUrl', $responseData);
    }

    public function testAdminCanCreateLesson(): void
    {
        $this->client->loginUser($this->admin);

        $cursus = new Cursus();
        $cursus->setName('Cursus Test');
        $cursus->setPrice(99.99);
        $this->em->persist($cursus);
        $this->em->flush();

        $payload = [
            'name' => 'Introduction à PHP',
            'price' => 29.99,
            'description' => 'Une leçon d\'introduction à PHP',
            'videoUrl' => 'https://example.com/video1.mp4',
            'cursus' => '/api/cursus/' . $cursus->getId()
        ];

        $this->client->request(
            'POST',
            '/api/lesson',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Introduction à PHP', $responseData['name']);
        $this->assertEquals(29.99, $responseData['price']);
        $this->assertEquals('Une leçon d\'introduction à PHP', $responseData['description']);
        $this->assertEquals('https://example.com/video1.mp4', $responseData['videoUrl']);

        $lesson = $this->em->getRepository(Lesson::class)->findOneBy(['name' => 'Introduction à PHP']);
        $this->assertNotNull($lesson);
        $this->assertEquals($cursus->getId(), $this->em->getRepository(Lesson::class)->findOneBy(['name' => 'Introduction à PHP'])->getCursus()->getId());
    }

    public function testNonAdminCannotCreateLesson(): void
    {
        $this->client->loginUser($this->user);

        $payload = [
            'name' => 'Introduction à PHP',
            'price' => 29.99,
            'description' => 'Une leçon d\'introduction à PHP',
            'videoUrl' => 'https://example.com/video1.mp4'
        ];

        $this->client->request(
            'POST',
            '/api/lesson',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }



    public function testAdminCanUpdateLesson(): void
    {
        $lesson = new Lesson();
        $lesson->setName('Lesson Original');
        $lesson->setPrice(19.99);
        $lesson->setDescription('Description originale');
        $lesson->setVideoUrl('https://example.com/original.mp4');
        $lesson->setCursus($this->cursus);
        $this->em->persist($lesson);
        $this->em->flush();

        $this->client->loginUser($this->admin);

        $payload = [
            'name' => 'Lesson Modifiée',
            'price' => 39.99,
            'description' => 'Description modifiée',
            'videoUrl' => 'https://example.com/modified.mp4'
        ];

        $this->client->request(
            'PUT',
            '/api/lesson/' . $lesson->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Lesson Modifiée', $responseData['name']);
        $this->assertEquals(39.99, $responseData['price']);
        $this->assertEquals('Description modifiée', $responseData['description']);
        $this->assertEquals('https://example.com/modified.mp4', $responseData['videoUrl']);
    }

    public function testNonAdminCannotUpdateLesson(): void
    {
        $lesson = new Lesson();
        $lesson->setName('Lesson Original');
        $lesson->setPrice(19.99);
        $lesson->setDescription('Description originale');
        $lesson->setVideoUrl('https://example.com/original.mp4');
        $lesson->setCursus($this->cursus);
        $this->em->persist($lesson);
        $this->em->flush();

        $this->client->loginUser($this->user);

        $payload = [
            'name' => 'Lesson Modifiée',
            'price' => 39.99,
            'description' => 'Description modifiée',
            'videoUrl' => 'https://example.com/modified.mp4'
        ];

        $this->client->request(
            'PUT',
            '/api/lesson/' . $lesson->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testAdminCanDeleteLesson(): void
    {
        $lesson = new Lesson();
        $lesson->setName('Lesson à supprimer');
        $lesson->setPrice(19.99);
        $lesson->setDescription('Description');
        $lesson->setVideoUrl('https://example.com/video.mp4');
        $lesson->setCursus($this->cursus);
        $this->em->persist($lesson);
        $this->em->flush();

        $lessonId = $lesson->getId();

        $this->client->loginUser($this->admin);
        $this->client->request('DELETE', '/api/lesson/' . $lessonId);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $deletedLesson = $this->em->getRepository(Lesson::class)->find($lessonId);
        $this->assertNull($deletedLesson);
    }
}
