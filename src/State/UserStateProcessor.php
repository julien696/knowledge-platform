<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Enum\UserRole;
use App\Message\SendConfirmationEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;

class UserStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer,
        private Security $security,
        private MessageBusInterface $bus
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?User
    {
        if ($operation instanceof DeleteOperationInterface) {
            $this->em->remove($data);
            $this->em->flush();
            return null;
        }

        if (!$data instanceof User) {
            return $data;
        }

        if ($operation->getName() === 'user_register') {
            return $this->handleRegistration($data);
        }

        if ($operation->getName() === 'post' && str_contains($context['uri'] ?? '', '/admin/users')) {
            return $this->handleAdminCreateUser($data);
        }

        if (in_array($operation->getName(), ['put', 'patch'], true)) {
            return $this->handleUpdateUser($data, $context);
        }

        return $data;
    }

    private function handleRegistration(User $user): User
    {
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

        if ($existingUser) {
            throw new \InvalidArgumentException('Cet email est déjà utilisé');
        }

        $user->setRole(UserRole::USER);
        $user->setIsVerified(false);
        
        $this->processPassword($user);
        $this->setConfirmationToken($user);
        
        $this->em->persist($user);
        $this->em->flush();

        $this->bus->dispatch(new SendConfirmationEmailMessage($user->getId()));

        return $user;
    }

    private function handleAdminCreateUser(User $user): User
    {
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

        if ($existingUser) {
            throw new \InvalidArgumentException('Cet email est déjà utilisé');
        }

        if (!$user->getRole()) {
            $user->setRole(UserRole::USER);
        }
        
        if (empty($user->getPlainPassword())) {
            throw new \InvalidArgumentException('Un mot de passe est requis pour créer un utilisateur');
        }
        
        $this->processPassword($user);
        
        if ($user->isVerified() === null) {
            $user->setIsVerified(true);
        }
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }

    private function handleUpdateUser(User $user, array $context): User
    {
        $originalData = $context['previous_data'] ?? null;
        
        if ($user->getPlainPassword()) {
            $this->processPassword($user);
        } elseif ($originalData && !empty($originalData->getPassword())) {
            $user->setPassword($originalData->getPassword());
        }
        
        if (!$this->security->isGranted('ROLE_ADMIN') && $originalData) {
            $user->setRole($originalData->getRole());
        }
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }

    private function processPassword(User $user): void
    {
        if ($user->getPlainPassword()) {
            $hashed = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashed);
            $user->eraseCredentials();
        }
    }

    private function setConfirmationToken(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $user->setConfirmationToken($token);
    }
}
