<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261020120930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product publication status history and hide existing products by default.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ref_statut_produit (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, product_id INT NOT NULL, INDEX IDX_882C7644584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE ref_statut_produit ADD CONSTRAINT FK_882C7644584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql("INSERT INTO ref_statut_produit (status, created_at, product_id) SELECT 'masque', CURRENT_TIMESTAMP, id FROM product");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ref_statut_produit DROP FOREIGN KEY FK_882C7644584665A');
        $this->addSql('DROP TABLE ref_statut_produit');
    }
}
