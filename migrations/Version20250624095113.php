<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624095113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE card (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', collection_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, artist_tag VARCHAR(80) NOT NULL, rarity VARCHAR(255) NOT NULL, release_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', drop_rate DOUBLE PRECISION NOT NULL, INDEX IDX_161498D3514956FD (collection_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE card_collection (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', owner_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, display_image VARCHAR(255) NOT NULL, booster_image VARCHAR(255) NOT NULL, release_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', end_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_special TINYINT(1) NOT NULL, INDEX IDX_903FF83C7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_account (id INT AUTO_INCREMENT NOT NULL, user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', account_id VARCHAR(255) NOT NULL, provider VARCHAR(255) NOT NULL, INDEX IDX_6E30F9D1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, profile_picture VARCHAR(100) NOT NULL, username VARCHAR(120) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_card (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', owner_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', card_template_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', obtained_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', obtained_from VARCHAR(50) NOT NULL, INDEX IDX_6C95D41A7E3C61F9 (owner_id), INDEX IDX_6C95D41AE20E5022 (card_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card ADD CONSTRAINT FK_161498D3514956FD FOREIGN KEY (collection_id) REFERENCES card_collection (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_collection ADD CONSTRAINT FK_903FF83C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_account ADD CONSTRAINT FK_6E30F9D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card ADD CONSTRAINT FK_6C95D41A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card ADD CONSTRAINT FK_6C95D41AE20E5022 FOREIGN KEY (card_template_id) REFERENCES card (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE card DROP FOREIGN KEY FK_161498D3514956FD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_collection DROP FOREIGN KEY FK_903FF83C7E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_account DROP FOREIGN KEY FK_6E30F9D1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card DROP FOREIGN KEY FK_6C95D41A7E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card DROP FOREIGN KEY FK_6C95D41AE20E5022
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE card
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE card_collection
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE oauth_account
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_card
        SQL);
    }
}
