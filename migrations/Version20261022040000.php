<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022040000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store selected payment method and delivery details on each order.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE commande ADD payment_method VARCHAR(30) DEFAULT 'non_renseigne' NOT NULL, ADD delivery_phone VARCHAR(30) DEFAULT NULL, ADD delivery_address VARCHAR(255) DEFAULT NULL, ADD delivery_address_complement VARCHAR(255) DEFAULT NULL, ADD delivery_city VARCHAR(120) DEFAULT NULL, ADD delivery_postal_code VARCHAR(20) DEFAULT NULL, ADD delivery_country VARCHAR(120) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande DROP payment_method, DROP delivery_phone, DROP delivery_address, DROP delivery_address_complement, DROP delivery_city, DROP delivery_postal_code, DROP delivery_country');
    }
}
