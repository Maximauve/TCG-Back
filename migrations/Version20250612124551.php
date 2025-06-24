<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250612124551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE card ADD collection_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card ADD CONSTRAINT FK_161498D3514956FD FOREIGN KEY (collection_id) REFERENCES card_collection (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_161498D3514956FD ON card (collection_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE card DROP FOREIGN KEY FK_161498D3514956FD
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_161498D3514956FD ON card
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card DROP collection_id
        SQL);
    }
}
