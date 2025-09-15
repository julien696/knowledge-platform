<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250912154700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make timestamp columns nullable in the lesson table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lesson MODIFY created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson MODIFY updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE lesson SET created_at = NOW() WHERE created_at IS NULL');
        $this->addSql('UPDATE lesson SET updated_at = NOW() WHERE updated_at IS NULL');
        $this->addSql('ALTER TABLE lesson MODIFY created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE lesson MODIFY updated_at DATETIME NOT NULL');
    }
}
