<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260603082514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, location_id INT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, description LONGTEXT DEFAULT NULL, poster VARCHAR(255) DEFAULT NULL, fee VARCHAR(255) NOT NULL, comment LONGTEXT DEFAULT NULL, thematic VARCHAR(255) NOT NULL, public VARCHAR(255) NOT NULL, event_status VARCHAR(255) NOT NULL, INDEX IDX_3BAE0AA79E6B1585 (organisation_id), INDEX IDX_3BAE0AA764D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, town VARCHAR(255) NOT NULL, lat NUMERIC(10, 8) DEFAULT NULL, lon NUMERIC(11, 8) DEFAULT NULL, information LONGTEXT DEFAULT NULL, INDEX IDX_5E9E89CB9E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `organisation` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, town VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(30) NOT NULL, status VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA79E6B1585 FOREIGN KEY (organisation_id) REFERENCES `organisation` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB9E6B1585 FOREIGN KEY (organisation_id) REFERENCES `organisation` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `organisation` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA79E6B1585
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA764D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB9E6B1585
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE location
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `organisation`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_request
        SQL);
    }
}
