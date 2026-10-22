<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename ROLE_GESTIONNAIRE to ROLE_MANAGER.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE role SET code = 'ROLE_MANAGER', label = 'Manager' WHERE code = 'ROLE_GESTIONNAIRE'"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "UPDATE role SET code = 'ROLE_GESTIONNAIRE', label = 'Gestionnaire' WHERE code = 'ROLE_MANAGER'"
        );
    }
}
