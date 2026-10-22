<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019134000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user role relation and seed the two supported roles.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO role (code, label, created_at) SELECT 'ROLE_ADMIN', 'Administrateur', CURRENT_TIMESTAMP WHERE NOT EXISTS (SELECT 1 FROM role WHERE code = 'ROLE_ADMIN')"
        );
        $this->addSql(
            "INSERT INTO role (code, label, created_at) SELECT 'ROLE_USER', 'Utilisateur', CURRENT_TIMESTAMP WHERE NOT EXISTS (SELECT 1 FROM role WHERE code = 'ROLE_USER')"
        );
        $this->addSql('ALTER TABLE `user` ADD role_id INT DEFAULT NULL');
        $this->addSql(
            "UPDATE `user` SET role_id = CASE WHEN JSON_CONTAINS(roles, '\"ROLE_ADMIN\"') THEN (SELECT id FROM role WHERE code = 'ROLE_ADMIN' ORDER BY id ASC LIMIT 1) ELSE (SELECT id FROM role WHERE code = 'ROLE_USER' ORDER BY id ASC LIMIT 1) END"
        );
        $this->addSql('ALTER TABLE `user` MODIFY role_id INT NOT NULL');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_USER_ROLE FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('CREATE INDEX IDX_USER_ROLE ON `user` (role_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_USER_ROLE');
        $this->addSql('DROP INDEX IDX_USER_ROLE ON `user`');
        $this->addSql('ALTER TABLE `user` DROP role_id');
    }
}
