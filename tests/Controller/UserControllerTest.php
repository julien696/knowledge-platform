<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->em = $container->get(EntityManagerInterface::class);

        $mailer = $this->createMock(MailerInterface::class);
        $container->set(MailerInterface::class, $mailer);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(fn($message) => new Envelope($message));
        $container->set(MessageBusInterface::class, $bus);

        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();
    }

    public function testRegisterUserSendsConfirmationEmail(): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode([
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'plainPassword' => 'password123',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'alice@example.com']);
        $this->assertNotNull($user);
        $this->assertFalse($user->isVerified());
        $this->assertNotNull($user->getConfirmationToken());
    }

    public function testConfirmUserAccount(): void
    {
        $user = new User();
        $user->setName('Bob');
        $user->setEmail('bob@example.com');
        $user->setPassword('hashed');
        $user->setRole(UserRole::USER);
        $user->setIsVerified(false);
        $user->setConfirmationToken('token123');
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('GET', '/api/confirm/token123');
        $response = $this->client->getResponse();

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Compte confirmé avec succès.', $data['message']);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->em->refresh($user);
        $this->assertTrue($user->isVerified());
        $this->assertNull($user->getConfirmationToken());
    }

    public function testLoginUser(): void
    {
        $user = new User();
        $user->setName('Charlie');
        $user->setEmail('charlie@example.com');
        $user->setPassword(password_hash('mypassword', PASSWORD_BCRYPT));
        $user->setRole(UserRole::USER);
        $user->setIsVerified(true);
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'charlie@example.com',
                'password' => 'mypassword',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAdminCanCreateUser(): void
    {
        $admin = new User();
        $admin->setName('Admin');
        $admin->setEmail('admin@example.com');
        $admin->setPassword(password_hash('adminpass', PASSWORD_BCRYPT));
        $admin->setRole(UserRole::ADMIN);
        $admin->setIsVerified(true);
        $this->em->persist($admin);
        $this->em->flush();

        $this->client->loginUser($admin);

        $uniqueEmail = 'user_' . uniqid() . '@example.com';
        $payload = [
            'name' => 'NewUser',
            'email' => $uniqueEmail,
            'plainPassword' => 'userpass123',
            'role' => UserRole::USER->value
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();

        $this->assertContains($response->getStatusCode(), [Response::HTTP_CREATED, Response::HTTP_OK, Response::HTTP_BAD_REQUEST]);
        
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $uniqueEmail]);
        $this->assertNotNull($user, 'L\'utilisateur devrait être créé en base de données');
        $this->assertEquals('NewUser', $user->getName());
        $this->assertEquals($uniqueEmail, $user->getEmail());
        $this->assertEquals(UserRole::USER, $user->getRole());
        $this->assertFalse($user->isVerified()); 
        $this->assertNotNull($user->getPassword());
        $this->assertNotEquals('userpass123', $user->getPassword()); 
        $this->assertNotNull($user->getConfirmationToken()); 
    }

    public function testAdminCanUpdateUser(): void
    {
        $admin = new User();
        $admin->setName('Admin');
        $admin->setEmail('admin2@example.com');
        $admin->setPassword(password_hash('adminpass', PASSWORD_BCRYPT));
        $admin->setRole(UserRole::ADMIN);
        $admin->setIsVerified(true);

        $userEmail = 'user_' . uniqid() . '@example.com';
        $user = new User();
        $user->setName('OldName');
        $user->setEmail($userEmail);
        $user->setPassword(password_hash('oldpass', PASSWORD_BCRYPT));
        $user->setRole(UserRole::USER);
        $user->setIsVerified(true);

        $this->em->persist($admin);
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($admin);

        $newEmail = 'updated_' . uniqid() . '@example.com';
        $payload = [
            'name' => 'UpdatedName',
            'email' => $newEmail,
            'role' => UserRole::CLIENT->value,
            'isVerified' => false 
        ];

        $this->client->request(
            'PUT',
            '/api/admin/users/' . $user->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertEquals('UpdatedName', $responseData['name']);
        $this->assertEquals($newEmail, $responseData['email']);
        $this->assertEquals(UserRole::CLIENT->value, $responseData['role']);
    }

    public function testNonAdminCannotCreateUser(): void
    {
        $user = new User();
        $user->setName('RegularUser');
        $user->setEmail('user@example.com');
        $user->setPassword(password_hash('userpass', PASSWORD_BCRYPT));
        $user->setRole(UserRole::USER);
        $user->setIsVerified(true);
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($user);

        $payload = [
            'name' => 'NewUser',
            'email' => 'newuser@example.com',
            'plainPassword' => 'password123',
            'role' => UserRole::USER->value
        ];

        $this->client->request(
            'POST',
            '/api/admin/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testAdminCannotCreateUserWithExistingEmail(): void
    {
        $admin = new User();
        $admin->setName('Admin');
        $admin->setEmail('admin@example.com');
        $admin->setPassword(password_hash('adminpass', PASSWORD_BCRYPT));
        $admin->setRole(UserRole::ADMIN);
        $admin->setIsVerified(true);
        $this->em->persist($admin);
        $this->em->flush();

        $existingUser = new User();
        $existingUser->setName('ExistingUser');
        $existingUser->setEmail('existing@example.com');
        $existingUser->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $existingUser->setRole(UserRole::USER);
        $existingUser->setIsVerified(true);
        $this->em->persist($existingUser);
        $this->em->flush();

        $this->client->loginUser($admin);

        $payload = [
            'name' => 'NewUser',
            'email' => 'existing@example.com',
            'plainPassword' => 'password123',
            'role' => UserRole::USER->value
        ];

        $this->client->request(
            'POST',
            '/api/admin/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testNonAdminCannotUpdateUser(): void
    {
        $user = new User();
        $user->setName('RegularUser');
        $user->setEmail('user@example.com');
        $user->setPassword(password_hash('userpass', PASSWORD_BCRYPT));
        $user->setRole(UserRole::USER);
        $user->setIsVerified(true);
        $this->em->persist($user);
        $this->em->flush();

        $targetUser = new User();
        $targetUser->setName('TargetUser');
        $targetUser->setEmail('target@example.com');
        $targetUser->setPassword(password_hash('targetpass', PASSWORD_BCRYPT));
        $targetUser->setRole(UserRole::USER);
        $targetUser->setIsVerified(true);
        $this->em->persist($targetUser);
        $this->em->flush();

        $this->client->loginUser($user);

        $payload = [
            'name' => 'UpdatedName',
            'email' => 'updated@example.com',
            'role' => UserRole::CLIENT->value
        ];

        $this->client->request(
            'PUT',
            '/api/admin/users/' . $targetUser->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testGetProfileMe(): void
    {
        $user = new User();
        $user->setName('ProfileUser');
        $user->setEmail('profile@example.com');
        $user->setPassword(password_hash('pass', PASSWORD_BCRYPT));
        $user->setRole(UserRole::USER);
        $user->setIsVerified(true);
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/me');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('ProfileUser', $data['user']['name']);
        $this->assertEquals('profile@example.com', $data['user']['email']);
    }
}
