<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020062138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create and seed the order status reference table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE order_status (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_B88F75C977153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql("INSERT INTO order_status (code, label, created_at) VALUES
            ('PENDING', 'En attente', CURRENT_TIMESTAMP),
            ('PAID', 'Payée', CURRENT_TIMESTAMP),
            ('PROCESSING', 'En préparation', CURRENT_TIMESTAMP),
            ('SHIPPED', 'Expédiée', CURRENT_TIMESTAMP),
            ('DELIVERED', 'Livrée', CURRENT_TIMESTAMP),
            ('CANCELLED', 'Annulée', CURRENT_TIMESTAMP),
            ('REFUNDED', 'Remboursée', CURRENT_TIMESTAMP)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE order_status');
    }
}
