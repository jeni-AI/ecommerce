<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Activate existing accounts and default new accounts to active.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE user SET is_valid = 1');
        $this->addSql('ALTER TABLE user ALTER is_valid SET DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ALTER is_valid DROP DEFAULT');
    }
}
