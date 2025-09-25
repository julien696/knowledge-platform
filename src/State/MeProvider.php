<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Me;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class MeProvider implements ProviderInterface
{
    public function __construct(private Security $security) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Me
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user) {
            return null;
        }

        return new Me(
            $user,
            $user->getEnrollmentCursuses(),
            $user->getEnrollmentLessons(),
            $user->getOrders(),
            $user->getThemes(),
            $user->getCertifications()
        );
    }
   
}