<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031132953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE organisation ALTER address SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE organisation ALTER phone SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE organisation ALTER phone TYPE VARCHAR(30)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE organisation ALTER address DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE organisation ALTER phone DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE organisation ALTER phone TYPE VARCHAR(20)
        SQL);
    }
}
