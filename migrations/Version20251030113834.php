<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030113834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP CONSTRAINT fk_3bae0aa7da6a219
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE place_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE location (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) NOT NULL, town VARCHAR(255) NOT NULL, lon VARCHAR(10) DEFAULT NULL, lat VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE place
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_3bae0aa7da6a219
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP end_time
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event RENAME COLUMN place_id TO location_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3BAE0AA764D218E ON event (location_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA764D218E
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE place_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE place (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) NOT NULL, town VARCHAR(255) NOT NULL, lon VARCHAR(10) DEFAULT NULL, lat VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE location
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3BAE0AA764D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD end_time DATE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event RENAME COLUMN location_id TO place_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT fk_3bae0aa7da6a219 FOREIGN KEY (place_id) REFERENCES place (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_3bae0aa7da6a219 ON event (place_id)
        SQL);
    }
}
