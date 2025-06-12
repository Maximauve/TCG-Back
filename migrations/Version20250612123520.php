<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250612123520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, artist_tag VARCHAR(80) NOT NULL, rarity VARCHAR(255) NOT NULL, release_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', drop_rate DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE card_collection (id INT AUTO_INCREMENT NOT NULL, owner_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, display_image VARCHAR(255) NOT NULL, booster_image VARCHAR(255) NOT NULL, release_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', end_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_special TINYINT(1) NOT NULL, INDEX IDX_903FF83C7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_card (user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', card_id INT NOT NULL, INDEX IDX_6C95D41AA76ED395 (user_id), INDEX IDX_6C95D41A4ACC9A20 (card_id), PRIMARY KEY(user_id, card_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_collection ADD CONSTRAINT FK_903FF83C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card ADD CONSTRAINT FK_6C95D41AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card ADD CONSTRAINT FK_6C95D41A4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE card_collection DROP FOREIGN KEY FK_903FF83C7E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card DROP FOREIGN KEY FK_6C95D41AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card DROP FOREIGN KEY FK_6C95D41A4ACC9A20
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE card
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE card_collection
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_card
        SQL);
    }
}
