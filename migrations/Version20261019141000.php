<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019141000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category creation timestamps.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category ADD created_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE category SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL');
        $this->addSql('ALTER TABLE category MODIFY created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category DROP created_at');
    }
}
