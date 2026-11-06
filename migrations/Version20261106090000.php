<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Services : deuxième image (facultative).
 */
final class Version20261106090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Services: colonne image2_url (deuxième image)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE services ADD image2_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE services DROP image2_url');
    }
}
