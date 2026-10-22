<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019134500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align the user role index name with Doctrine.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` RENAME INDEX IDX_USER_ROLE TO IDX_8D93D649D60322AC');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` RENAME INDEX IDX_8D93D649D60322AC TO IDX_USER_ROLE');
    }
}
