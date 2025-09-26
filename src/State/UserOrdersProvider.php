<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\SecurityBundle\Security;

class UserOrdersProvider implements ProviderInterface
{
    public function __construct(
        private OrderRepository $orderRepository,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            return [];
        }

        return $this->orderRepository->findBy(
            ['user' => $user], 
            ['date' => 'DESC']
        );
    }
}

