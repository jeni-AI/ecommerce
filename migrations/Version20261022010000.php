<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace ROLE_USER with ROLE_CLIENT and add ROLE_GESTIONNAIRE.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO role (code, label, created_at) SELECT 'ROLE_CLIENT', 'Client', CURRENT_TIMESTAMP WHERE NOT EXISTS (SELECT 1 FROM role WHERE code = 'ROLE_CLIENT')"
        );
        $this->addSql(
            "INSERT INTO role (code, label, created_at) SELECT 'ROLE_GESTIONNAIRE', 'Gestionnaire', CURRENT_TIMESTAMP WHERE NOT EXISTS (SELECT 1 FROM role WHERE code = 'ROLE_GESTIONNAIRE')"
        );
        $this->addSql(
            "UPDATE `user` SET role_id = (SELECT id FROM role WHERE code = 'ROLE_CLIENT' ORDER BY id ASC LIMIT 1) WHERE role_id IN (SELECT id FROM role WHERE code = 'ROLE_USER')"
        );
        $this->addSql("DELETE FROM role WHERE code = 'ROLE_USER'");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ROLE_CODE ON role (code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_ROLE_CODE ON role');
        $this->addSql(
            "INSERT INTO role (code, label, created_at) SELECT 'ROLE_USER', 'Utilisateur', CURRENT_TIMESTAMP WHERE NOT EXISTS (SELECT 1 FROM role WHERE code = 'ROLE_USER')"
        );
        $this->addSql(
            "UPDATE `user` SET role_id = (SELECT id FROM role WHERE code = 'ROLE_USER' ORDER BY id ASC LIMIT 1) WHERE role_id IN (SELECT id FROM role WHERE code IN ('ROLE_CLIENT', 'ROLE_GESTIONNAIRE'))"
        );
        $this->addSql("DELETE FROM role WHERE code IN ('ROLE_CLIENT', 'ROLE_GESTIONNAIRE')");
    }
}
