<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Cursus;
use App\Repository\CursusRepository;
use App\Repository\EnrollmentCursusRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CursusContentProvider implements ProviderInterface
{
    public function __construct(
        private CursusRepository $cursusRepository,
        private EnrollmentCursusRepository $enrollmentRepository,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Cursus
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            throw new AccessDeniedHttpException('Utilisateur non authentifié');
        }

        $cursusId = $uriVariables['id'] ?? null;
        
        if (!$cursusId) {
            return null;
        }

        $cursus = $this->cursusRepository->find($cursusId);
        
        if (!$cursus) {
            return null;
        }

        $enrollment = $this->enrollmentRepository->findOneBy([
            'user' => $user,
            'cursus' => $cursus
        ]);

        if (!$enrollment) {
            throw new AccessDeniedHttpException('Vous n\'avez pas accès à ce contenu');
        }

        return $cursus;
    }
}
