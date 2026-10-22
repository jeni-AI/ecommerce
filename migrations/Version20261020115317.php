<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261020115317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace the command status reference with a fixed status field.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE commande ADD status VARCHAR(20) NOT NULL DEFAULT 'en_attente'");
        $this->addSql("UPDATE commande c JOIN order_status s ON s.id = c.status_id SET c.status = CASE s.code WHEN 'PENDING' THEN 'en_attente' WHEN 'PROCESSING' THEN 'en_cours' WHEN 'PAID' THEN 'validee' WHEN 'SHIPPED' THEN 'en_cours' WHEN 'DELIVERED' THEN 'validee' WHEN 'CANCELLED' THEN 'non_validee' WHEN 'REFUNDED' THEN 'non_validee' ELSE 'en_attente' END");
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `FK_6EEAA67D6BF700BD`');
        $this->addSql('DROP INDEX IDX_6EEAA67D6BF700BD ON commande');
        $this->addSql('ALTER TABLE commande DROP status_id');
        $this->addSql('DROP TABLE order_status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE order_status (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_B88F75C977153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql("INSERT INTO order_status (code, label, created_at) VALUES ('PENDING', 'En attente', CURRENT_TIMESTAMP), ('PROCESSING', 'En préparation', CURRENT_TIMESTAMP), ('PAID', 'Payée', CURRENT_TIMESTAMP), ('SHIPPED', 'Expédiée', CURRENT_TIMESTAMP), ('DELIVERED', 'Livrée', CURRENT_TIMESTAMP), ('CANCELLED', 'Annulée', CURRENT_TIMESTAMP), ('REFUNDED', 'Remboursée', CURRENT_TIMESTAMP)");
        $this->addSql('ALTER TABLE commande ADD status_id INT DEFAULT NULL');
        $this->addSql("UPDATE commande c JOIN order_status s ON s.code = CASE c.status WHEN 'en_attente' THEN 'PENDING' WHEN 'en_cours' THEN 'PROCESSING' WHEN 'validee' THEN 'DELIVERED' WHEN 'non_validee' THEN 'CANCELLED' ELSE 'PENDING' END SET c.status_id = s.id");
        $this->addSql('ALTER TABLE commande MODIFY status_id INT NOT NULL, DROP status');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `FK_6EEAA67D6BF700BD` FOREIGN KEY (status_id) REFERENCES order_status (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6EEAA67D6BF700BD ON commande (status_id)');
    }
}
