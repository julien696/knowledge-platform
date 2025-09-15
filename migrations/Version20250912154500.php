<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250912154500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make created_at and updated_at columns nullable in all tables';
    }

    public function up(Schema $schema): void
    {
        // This migration will modify all tables that have created_at/updated_at columns
        $tables = $this->connection->createSchemaManager()->listTableNames();
        
        foreach ($tables as $tableName) {
            $this->addSql(sprintf('ALTER TABLE %s MODIFY created_at DATETIME DEFAULT NULL', $tableName));
            $this->addSql(sprintf('ALTER TABLE %s MODIFY updated_at DATETIME DEFAULT NULL', $tableName));
        }
    }

    public function down(Schema $schema): void
    {
        // This is the reverse operation - making columns NOT NULL again
        $tables = $this->connection->createSchemaManager()->listTableNames();
        
        foreach ($tables as $tableName) {
            $this->addSql(sprintf('UPDATE %s SET created_at = NOW() WHERE created_at IS NULL', $tableName));
            $this->addSql(sprintf('UPDATE %s SET updated_at = NOW() WHERE updated_at IS NULL', $tableName));
            $this->addSql(sprintf('ALTER TABLE %s MODIFY created_at DATETIME NOT NULL', $tableName));
            $this->addSql(sprintf('ALTER TABLE %s MODIFY updated_at DATETIME NOT NULL', $tableName));
        }
    }
}
