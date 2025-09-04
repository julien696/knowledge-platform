<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250904151954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certification CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE `order` ADD updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP upadated_at');
        $this->addSql('ALTER TABLE order_item ADD updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE theme ADD updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certification CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE theme DROP updated_at');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE order_item DROP updated_at');
        $this->addSql('ALTER TABLE `order` ADD upadated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP updated_at');
    }
}
