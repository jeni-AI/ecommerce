<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019142200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align the category code index name with Doctrine.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category RENAME INDEX UNIQ_CATEGORY_CODE TO UNIQ_64C19C177153098');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category RENAME INDEX UNIQ_64C19C177153098 TO UNIQ_CATEGORY_CODE');
    }
}
