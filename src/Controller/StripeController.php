<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use App\Service\StripeService;
use App\Service\EnrollmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private OrderRepository $orderRepository,
        private EntityManagerInterface $em,
        private Security $security,
        private EnrollmentService $enrollmentService) {}

    #[Route('/api/stripe/create-payment-intent', name: 'create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(): JsonResponse
    {
        $paymentIntent = $this->stripeService->createPaymentIntent(2000, 'eur');

        return new JsonResponse([
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }

    #[Route('/api/stripe/create-checkout-session', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(): JsonResponse
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['orderId'])) {
            return new JsonResponse(['error' => 'orderId is required'], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderRepository->find($data['orderId']);

        if (!$order) {
            return new JsonResponse(['error' => 'Commande introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($order->getUser() !== $this->security->getUser() && !$this->security->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }
        
        $session = $this->stripeService->createCheckoutSession($order);

        return new JsonResponse([
            'session_id' => $session->id,
            'url' => $session->url
        ]);
    }

    #[Route('/api/orders/{id}/stripe', name: 'pay_order', methods: ['POST'])]
    public function payOrder(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if(!$order) {
            return new JsonResponse(['error' => 'Commande Introuvable'], Response::HTTP_NOT_FOUND);
        }

        if($order->getUser() !== $this->security->getUser() && !$this->security->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        if($order->getStripeOrderId()) {
            $clientSecret = $this->stripeService->retrievePaymentIntentClientSecret($order->getStripeOrderId());
        } else {
            $paymentIntent = $this->stripeService->createPaymentIntent((float)$order->getAmount());

            $order->setStripeOrderId($paymentIntent->id);
            $this->em->flush();

            $clientSecret = $paymentIntent->client_secret;
        }
        
        return new JsonResponse([
            'client_secret' => $clientSecret
        ]);
    }

    #[Route('/api/orders/{id}/confirm-payment', name: 'confirm_payment', methods: ['POST'])]
    public function confirmPayment(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if(!$order) {
            return new JsonResponse(['error' => 'Commande Introuvable'], Response::HTTP_NOT_FOUND);
        }

        if($order->getUser() !== $this->security->getUser() && !$this->security->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        if($order->getStatus() === 'paid') {
            return new JsonResponse([
                'message' => 'Commande déjà payée',
                'order_id' => $order->getId(),
                'status' => 'already_paid'
            ]);
        }

        $order->setStatus('paid');
        
        $this->enrollmentService->createEnrollmentsFromOrder($order);
        
        $this->em->flush();

        $purchasedItems = [];
        foreach ($order->getOrderItems() as $item) {
            if ($item->getLesson()) {
                $purchasedItems[] = [
                    'type' => 'lesson',
                    'id' => $item->getLesson()->getId(),
                    'name' => $item->getLesson()->getName()
                ];
            }
            if ($item->getCursus()) {
                $purchasedItems[] = [
                    'type' => 'cursus',
                    'id' => $item->getCursus()->getId(),
                    'name' => $item->getCursus()->getName()
                ];
            }
        }

        return new JsonResponse([
            'message' => 'Paiement confirmé et accès accordé',
            'order_id' => $order->getId(),
            'status' => 'paid',
            'purchased_items' => $purchasedItems
        ]);
    }

    #[Route('/api/check-access/lesson/{id}', name: 'check_lesson_access', methods: ['GET'])]
    public function checkLessonAccess(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        
        if (!$user) {
            return new JsonResponse(['has_access' => false, 'reason' => 'not_authenticated']);
        }

        $hasAccess = $this->enrollmentService->hasAccessToLesson($user, $id);
        
        return new JsonResponse([
            'has_access' => $hasAccess,
            'lesson_id' => $id,
            'user_id' => $user->getId()
        ]);
    }

    #[Route('/api/check-access/cursus/{id}', name: 'check_cursus_access', methods: ['GET'])]
    public function checkCursusAccess(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        
        if (!$user) {
            return new JsonResponse(['has_access' => false, 'reason' => 'not_authenticated']);
        }

        $hasAccess = $this->enrollmentService->hasAccessToCursus($user, $id);
        
        return new JsonResponse([
            'has_access' => $hasAccess,
            'cursus_id' => $id,
            'user_id' => $user->getId()
        ]);
    }
}
