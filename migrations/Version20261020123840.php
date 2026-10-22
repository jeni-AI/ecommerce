<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Keep only the most recent status for each command and product.
 */
final class Version20261020123840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Keep one current status per command and product, and enforce this at database level.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE older FROM ref_statut_cmd older INNER JOIN ref_statut_cmd newer ON older.commande_id = newer.commande_id AND (older.created_at < newer.created_at OR (older.created_at = newer.created_at AND older.id < newer.id))');
        $this->addSql('DELETE older FROM ref_statut_produit older INNER JOIN ref_statut_produit newer ON older.product_id = newer.product_id AND (older.created_at < newer.created_at OR (older.created_at = newer.created_at AND older.id < newer.id))');
        $this->addSql('ALTER TABLE ref_statut_cmd DROP INDEX IDX_45D67F6C82EA2E54, ADD UNIQUE INDEX UNIQ_45D67F6C82EA2E54 (commande_id)');
        $this->addSql('ALTER TABLE ref_statut_produit DROP INDEX IDX_882C7644584665A, ADD UNIQUE INDEX UNIQ_882C7644584665A (product_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ref_statut_cmd DROP INDEX UNIQ_45D67F6C82EA2E54, ADD INDEX IDX_45D67F6C82EA2E54 (commande_id)');
        $this->addSql('ALTER TABLE ref_statut_produit DROP INDEX UNIQ_882C7644584665A, ADD INDEX IDX_882C7644584665A (product_id)');
    }
}
