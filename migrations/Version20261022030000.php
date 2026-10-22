<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align the role code unique index name with the Doctrine mapping.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role RENAME INDEX UNIQ_ROLE_CODE TO UNIQ_57698A6A77153098');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role RENAME INDEX UNIQ_57698A6A77153098 TO UNIQ_ROLE_CODE');
    }
}
