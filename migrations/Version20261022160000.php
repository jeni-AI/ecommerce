<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Backfill client delivery details from their most recent order.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO client (
                user_id,
                telephone,
                delivery_address,
                delivery_address_complement,
                delivery_city,
                delivery_postal_code,
                delivery_country,
                created_at,
                updated_at
            )
            SELECT
                orders.client_id,
                orders.delivery_phone,
                orders.delivery_address,
                orders.delivery_address_complement,
                orders.delivery_city,
                orders.delivery_postal_code,
                orders.delivery_country,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            FROM commande orders
            LEFT JOIN client ON client.user_id = orders.client_id
            WHERE client.id IS NULL
              AND orders.id = (
                  SELECT MAX(latest_order.id)
                  FROM commande latest_order
                  WHERE latest_order.client_id = orders.client_id
              )
            SQL);

        $this->addSql(<<<'SQL'
            UPDATE client
            INNER JOIN commande orders ON orders.client_id = client.user_id
            SET
                client.telephone = COALESCE(NULLIF(client.telephone, ''), orders.delivery_phone),
                client.delivery_address = COALESCE(NULLIF(client.delivery_address, ''), orders.delivery_address),
                client.delivery_address_complement = COALESCE(NULLIF(client.delivery_address_complement, ''), orders.delivery_address_complement),
                client.delivery_city = COALESCE(NULLIF(client.delivery_city, ''), orders.delivery_city),
                client.delivery_postal_code = COALESCE(NULLIF(client.delivery_postal_code, ''), orders.delivery_postal_code),
                client.delivery_country = COALESCE(NULLIF(client.delivery_country, ''), orders.delivery_country),
                client.updated_at = CURRENT_TIMESTAMP
            WHERE orders.id = (
                SELECT MAX(latest_order.id)
                FROM commande latest_order
                WHERE latest_order.client_id = client.user_id
            )
              AND (
                  client.telephone IS NULL OR client.telephone = ''
                  OR client.delivery_address IS NULL OR client.delivery_address = ''
                  OR client.delivery_address_complement IS NULL OR client.delivery_address_complement = ''
                  OR client.delivery_city IS NULL OR client.delivery_city = ''
                  OR client.delivery_postal_code IS NULL OR client.delivery_postal_code = ''
                  OR client.delivery_country IS NULL OR client.delivery_country = ''
              )
            SQL);
    }

    public function down(Schema $schema): void
    {
        // Backfilled customer data is retained to avoid deleting client profiles on rollback.
    }
}
