<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:update-timestamps',
    description: 'Update timestamps for existing records',
)]
class UpdateTimestampsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        
        // Get all entity classes that use TimestampableTrait
        $entities = [
            'App\Entity\Lesson',
            'App\Entity\Cursus',
            'App\Entity\Theme',
            'App\Entity\User',
            'App\Entity\EnrollmentLesson',
            'App\Entity\EnrollmentCursus',
            'App\Entity\Order',
            'App\Entity\OrderItem',
        ];
        
        foreach ($entities as $entityClass) {
            if (!class_exists($entityClass)) {
                continue;
            }
            
            $output->writeln(sprintf('Updating timestamps for %s...', $entityClass));
            
            $entitiesList = $this->entityManager->getRepository($entityClass)->findAll();
            
            foreach ($entitiesList as $entity) {
                if (method_exists($entity, 'getCreatedAt') && $entity->getCreatedAt() === null) {
                    $entity->setCreatedAt($now);
                }
                
                if (method_exists($entity, 'getUpdatedAt') && $entity->getUpdatedAt() === null) {
                    $entity->setUpdatedAt($now);
                }
            }
        }
        
        $this->entityManager->flush();
        $output->writeln('Timestamps updated successfully!');
        
        return Command::SUCCESS;
    }
}
