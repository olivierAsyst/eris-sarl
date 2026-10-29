<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * - Description des services en LONGTEXT (le VARCHAR(255) tronquait les textes)
 * - Lien réalisation -> service
 * - Champ updated_at sur les entités avec upload (nécessaire à VichUploader)
 * - Recalcule les slugs des services (sans accents ni caractères spéciaux)
 */
final class Version20261029080000 extends AbstractMigration
{
    private const UPLOAD_TABLES = ['services', 'realisation', '`member`', 'partner', 'image_galery', 'social_media', 'home_video'];

    public function getDescription(): string
    {
        return 'Services: description TEXT ; realisation.service_id ; updated_at sur les entités uploadables ; slugs propres';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE services CHANGE description description LONGTEXT NOT NULL');

        $this->addSql('ALTER TABLE realisation ADD service_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE realisation ADD CONSTRAINT FK_EAA5610EED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_EAA5610EED5CA9E6 ON realisation (service_id)');

        foreach (self::UPLOAD_TABLES as $table) {
            $this->addSql(sprintf("ALTER TABLE %s ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'", $table));
        }

        // Sauvegarde des messages du formulaire de contact
        $this->addSql("CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, subject_type VARCHAR(30) NOT NULL, full_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(30) DEFAULT NULL, organization VARCHAR(150) DEFAULT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ip VARCHAR(45) DEFAULT NULL, mail_sent TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_2C9211FE8B8E8428 (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function postUp(Schema $schema): void
    {
        $slugger = new AsciiSlugger('fr');
        $rows = $this->connection->fetchAllAssociative('SELECT id, name FROM services');
        foreach ($rows as $row) {
            $this->connection->update('services', [
                'slug' => $slugger->slug((string) $row['name'])->lower()->toString(),
            ], ['id' => $row['id']]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_message');

        foreach (self::UPLOAD_TABLES as $table) {
            $this->addSql(sprintf('ALTER TABLE %s DROP updated_at', $table));
        }

        $this->addSql('ALTER TABLE realisation DROP FOREIGN KEY FK_EAA5610EED5CA9E6');
        $this->addSql('DROP INDEX IDX_EAA5610EED5CA9E6 ON realisation');
        $this->addSql('ALTER TABLE realisation DROP service_id');

        // Attention : les descriptions de plus de 255 caractères seront tronquées
        $this->addSql('ALTER TABLE services CHANGE description description VARCHAR(255) NOT NULL');
    }
}
