<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022020000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create client profiles and persistent shopping carts.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, telephone VARCHAR(30) DEFAULT NULL, personal_address VARCHAR(255) DEFAULT NULL, personal_address_complement VARCHAR(255) DEFAULT NULL, personal_city VARCHAR(120) DEFAULT NULL, personal_postal_code VARCHAR(20) DEFAULT NULL, personal_country VARCHAR(120) DEFAULT NULL, delivery_address VARCHAR(255) DEFAULT NULL, delivery_address_complement VARCHAR(255) DEFAULT NULL, delivery_city VARCHAR(120) DEFAULT NULL, delivery_postal_code VARCHAR(20) DEFAULT NULL, delivery_country VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_CLIENT_USER (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_PANIER_CLIENT (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ligne_panier (id INT AUTO_INCREMENT NOT NULL, panier_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, UNIQUE INDEX UNIQ_LIGNE_PANIER_PRODUCT (panier_id, product_id), INDEX IDX_21691B4F77D927C (panier_id), INDEX IDX_21691B44584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_CLIENT_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_PANIER_CLIENT FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ligne_panier ADD CONSTRAINT FK_LIGNE_PANIER_PANIER FOREIGN KEY (panier_id) REFERENCES panier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ligne_panier ADD CONSTRAINT FK_LIGNE_PANIER_PRODUCT FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ligne_panier DROP FOREIGN KEY FK_LIGNE_PANIER_PANIER');
        $this->addSql('ALTER TABLE ligne_panier DROP FOREIGN KEY FK_LIGNE_PANIER_PRODUCT');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_PANIER_CLIENT');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_CLIENT_USER');
        $this->addSql('DROP TABLE ligne_panier');
        $this->addSql('DROP TABLE panier');
        $this->addSql('DROP TABLE client');
    }
}
