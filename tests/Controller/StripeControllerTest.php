<?php

namespace App\Tests\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Stripe\PaymentIntent;
use Symfony\Bundle\SecurityBundle\Security;
use App\Enum\UserRole;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class StripeControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private $stripeService;
    private $security;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = static::createClient();
        $container = $this->client->getContainer(); 
        $this->em = $container->get(EntityManagerInterface::class);

        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();

        $this->security = $this->createMock(Security::class);
        $container->set(Security::class, $this->security);

        $this->stripeService = $this->createMock(StripeService::class);
        $container->set(StripeService::class, $this->stripeService);
    }

    private function createUser(string $role = 'ROLE_USER'): User
    {
        $user = new User();
        $user->setName('Test User');
        $user->setEmail(uniqid('test_') . '@example.com');
        $user->setPassword('password');
        $user->setRole($role === 'ROLE_ADMIN' ? UserRole::ADMIN : UserRole::USER);

        $this->em->persist($user);
        return $user;
    }

    private function createOrder(User $user, float $amount = 20): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setAmount($amount);
        $order->setStatus('pending');
        $order->setDate(new \DateTime());

        $this->em->persist($order);
        return $order;
    }

    private function loginUser(User $user, bool $isAdmin = false): void
    {
        $this->security->method('getUser')->willReturn($user);
        $this->security->method('isGranted')->willReturn($isAdmin);
        $this->client->loginUser($user);
    }

    private function mockStripeSession(string $sessionId = 'cs_123', string $sessionUrl = 'https://checkout.stripe.com/c/pay/cs_123'): void
    {
        $sessionMock = new \stdClass();
        $sessionMock->id = $sessionId;
        $sessionMock->url = $sessionUrl;

        $this->stripeService
            ->method('createCheckoutSession')
            ->willReturn($sessionMock);
    }

    public function testCreateCheckoutSession(): void
    {
        $user = $this->createUser();
        $order = $this->createOrder($user);
        $this->em->flush();

        $this->loginUser($user);
        $this->mockStripeSession('cs_123', 'https://checkout.stripe.com/c/pay/cs_123');

        $this->client->request('POST', '/api/stripe/create-checkout-session', [], [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode(['orderId' => $order->getId()])
        );
        
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('cs_123', $data['session_id']);
        $this->assertEquals('https://checkout.stripe.com/c/pay/cs_123', $data['url']);
    }

    public function testConfirmPayment(): void
    {
        $user = $this->createUser();
        $order = $this->createOrder($user);
        $this->em->flush();

        $this->loginUser($user);

        $this->client->request('POST', '/api/orders/'.$order->getId().'/confirm-payment', [], [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode([])
        );
        
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Paiement confirmé avec succès', $data['message']);
    }

    public function testForbiddenAccess(): void
    {
        $userA = $this->createUser();
        $userB = $this->createUser();
        $order = $this->createOrder($userB);
        $this->em->flush();

        $this->loginUser($userA);

        $this->client->request('POST', '/api/stripe/create-checkout-session', [], [], 
            ['CONTENT_TYPE' => 'application/json'], 
            json_encode(['orderId' => $order->getId()])
        );
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

}
