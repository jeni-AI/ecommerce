<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261020112509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the commande table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL, total_amount INT NOT NULL, payment_status VARCHAR(20) NOT NULL, client_id INT NOT NULL, status_id INT NOT NULL, UNIQUE INDEX UNIQ_6EEAA67D77153098 (code), INDEX IDX_6EEAA67D19EB6921 (client_id), INDEX IDX_6EEAA67D6BF700BD (status_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D6BF700BD FOREIGN KEY (status_id) REFERENCES order_status (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D6BF700BD');
        $this->addSql('DROP TABLE commande');
    }
}
