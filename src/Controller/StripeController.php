<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Service\StripeService;
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
        private Security $security) {}

    #[Route('/api/stripe/create-payment-intent', name: 'create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(): JsonResponse
    {
        $paymentIntent = $this->stripeService->createPaymentIntent(2000, 'eur');

        return new JsonResponse([
            'client_secret' => $paymentIntent->client_secret,
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
}
