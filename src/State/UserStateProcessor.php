<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User) {
            return $data;
        }

        if (!$data->getRole()) {
            $data->setRole(UserRole::USER); 
        }

        if ($data->getPlainPassword()) {
            $hashed = $this->passwordHasher->hashPassword($data, $data->getPlainPassword());
            $data->setPassword($hashed);
            $data->eraseCredentials();
        }

        $data->setIsVerified(false);

        $token = bin2hex(random_bytes(32));
        $data->setConfirmationToken($token);

        $this->em->persist($data);
        $this->em->flush();

        $confirmationUrl = "https://ton-domaine.com/api/confirm/{$token}";

        $email = (new Email())
            ->from('no-reply@tonsite.com')
            ->to($data->getEmail())
            ->subject('Confirme ton compte')
            ->html("
                <p>Bonjour {$data->getName()},</p>
                <p>Clique sur ce lien pour confirmer ton compte :</p>
                <a href='{$confirmationUrl}'>Confirmer mon compte</a>
            ");

        $this->mailer->send($email);

        return $data;

    }
}
