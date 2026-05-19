<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260519190307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE location_organisation (location_id INT NOT NULL, organisation_id INT NOT NULL, PRIMARY KEY(location_id, organisation_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_974662DA64D218E ON location_organisation (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_974662DA9E6B1585 ON location_organisation (organisation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location_organisation ADD CONSTRAINT FK_974662DA64D218E FOREIGN KEY (location_id) REFERENCES Location (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location_organisation ADD CONSTRAINT FK_974662DA9E6B1585 FOREIGN KEY (organisation_id) REFERENCES "organisation" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_location_town
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location ADD information TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_event_core
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_event_fee
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_event_public
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_event_thematic
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location_organisation DROP CONSTRAINT FK_974662DA64D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location_organisation DROP CONSTRAINT FK_974662DA9E6B1585
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE location_organisation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_event_core ON event (start_date, location_id, organisation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_event_fee ON event (fee)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_event_public ON event (public)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_event_thematic ON event (thematic)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Location DROP information
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_location_town ON Location (town)
        SQL);
    }
}
