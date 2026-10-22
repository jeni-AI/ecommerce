<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261020115656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store command status changes with their dates and preserve existing statuses.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ref_statut_cmd (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, commande_id INT NOT NULL, INDEX IDX_45D67F6C82EA2E54 (commande_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE ref_statut_cmd ADD CONSTRAINT FK_45D67F6C82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO ref_statut_cmd (status, created_at, commande_id) SELECT status, CURRENT_TIMESTAMP, id FROM commande');
        $this->addSql('ALTER TABLE commande DROP status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE commande ADD status VARCHAR(20) DEFAULT 'en_attente' NOT NULL");
        $this->addSql('UPDATE commande c JOIN ref_statut_cmd r ON r.commande_id = c.id LEFT JOIN ref_statut_cmd newer ON newer.commande_id = r.commande_id AND newer.id > r.id SET c.status = r.status WHERE newer.id IS NULL');
        $this->addSql('ALTER TABLE ref_statut_cmd DROP FOREIGN KEY FK_45D67F6C82EA2E54');
        $this->addSql('DROP TABLE ref_statut_cmd');
    }
}
