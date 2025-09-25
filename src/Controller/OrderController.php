<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    #[Route('/api/create-order', name: 'create_order', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        

        $order = new Order();
        $order->setUser($user);
        $order->setAmount($data['amount']);
        $order->setDate(new \DateTime());
        
        if (isset($data['cursusId'])) {
            $cursus = $this->em->getRepository(\App\Entity\Cursus::class)->find($data['cursusId']);
            if ($cursus) {
                $orderItem = new OrderItem();
                $orderItem->setOrderId($order);
                $orderItem->setCursus($cursus);
                $orderItem->setPrice((string)$data['amount']);
                $order->addOrderItem($orderItem);
            }
        }
        
        if (isset($data['lessonId'])) {
            $lesson = $this->em->getRepository(\App\Entity\Lesson::class)->find($data['lessonId']);
            if ($lesson) {
                $orderItem = new OrderItem();
                $orderItem->setOrderId($order);
                $orderItem->setLesson($lesson);
                $orderItem->setPrice((string)$data['amount']);
                $order->addOrderItem($orderItem);
            }
        }
        
        $this->em->persist($order);
        $this->em->flush();
        
        return new JsonResponse([
            'orderId' => $order->getId(),
            'amount' => $order->getAmount()
        ]);
    }
}
