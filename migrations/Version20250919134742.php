<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250919134742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE enrollment_cursus ADD is_validated TINYINT(1) NOT NULL, ADD validated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE enrollment_lesson ADD is_validated TINYINT(1) NOT NULL, ADD validated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE enrollment_lesson DROP is_validated, DROP validated_at');
        $this->addSql('ALTER TABLE enrollment_cursus DROP is_validated, DROP validated_at');
    }
}
