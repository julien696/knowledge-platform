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

    private function createOrder(User $user, float $amount = 20, ?string $stripeOrderId = null): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setAmount($amount);
        $order->setStatus('pending');
        $order->setDate(new \DateTime());

        if ($stripeOrderId) {
            $order->setStripeOrderId($stripeOrderId);
        }

        $this->em->persist($order);
        return $order;
    }

    private function loginUser(User $user, bool $isAdmin = false): void
    {
        $this->security->method('getUser')->willReturn($user);
        $this->security->method('isGranted')->willReturn($isAdmin);
        $this->client->loginUser($user);
    }

    private function mockStripe(string $paymentId = 'pi_123', string $clientSecret = 'secret_123', bool $existing = false): void
    {
        if ($existing) {
            $this->stripeService
                ->method('retrievePaymentIntentClientSecret')
                ->willReturn($clientSecret);
        } else {
            $paymentIntentMock = PaymentIntent::constructFrom([
                'id' => $paymentId,
                'client_secret' => $clientSecret,
            ]);

            $this->stripeService
                ->method('createPaymentIntent')
                ->willReturn($paymentIntentMock);
        }
    }

    public function testNewPayment(): void
    {
        $user = $this->createUser();
        $order = $this->createOrder($user);
        $this->em->flush();

        $this->loginUser($user);
        $this->mockStripe('pi_123', 'secret_123');

        $this->client->request('POST', '/api/orders/'.$order->getId().'/stripe');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('secret_123', $data['client_secret']);

        $this->em->refresh($order);
        $this->assertEquals('pi_123', $order->getStripeOrderId());
    }

    public function testExistingPayment(): void
    {
        $user = $this->createUser();
        $order = $this->createOrder($user, 20, 'pi_existing');
        $this->em->flush();

        $this->loginUser($user);
        $this->mockStripe('pi_existing', 'secret_existing', true);

        $this->client->request('POST', '/api/orders/'.$order->getId().'/stripe');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('secret_existing', $data['client_secret']);
    }

    public function testForbiddenAccess(): void
    {
        $userA = $this->createUser();
        $userB = $this->createUser();
        $order = $this->createOrder($userB);
        $this->em->flush();

        $this->loginUser($userA);

        $this->client->request('POST', '/api/orders/'.$order->getId().'/stripe');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

}
