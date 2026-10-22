<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019142100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename category name to label and add unique category codes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category CHANGE name label VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE category ADD code VARCHAR(50) DEFAULT NULL');
        $this->addSql("UPDATE category SET code = CONCAT('CATEGORY-', id) WHERE code IS NULL");
        $this->addSql('ALTER TABLE category MODIFY code VARCHAR(50) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CATEGORY_CODE ON category (code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_CATEGORY_CODE ON category');
        $this->addSql('ALTER TABLE category DROP code');
        $this->addSql('ALTER TABLE category CHANGE label name VARCHAR(255) NOT NULL');
    }
}
