<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624123613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE card (id UUID NOT NULL, collection_id UUID NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, artist_tag VARCHAR(80) NOT NULL, rarity VARCHAR(255) NOT NULL, release_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, drop_rate DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_161498D3514956FD ON card (collection_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card.collection_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card.release_date IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE card_collection (id UUID NOT NULL, owner_id UUID NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, display_image VARCHAR(255) NOT NULL, booster_image VARCHAR(255) NOT NULL, release_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_special BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_903FF83C7E3C61F9 ON card_collection (owner_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card_collection.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card_collection.owner_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card_collection.release_date IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN card_collection.end_date IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_account (id SERIAL NOT NULL, user_id UUID NOT NULL, account_id VARCHAR(255) NOT NULL, provider VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6E30F9D1A76ED395 ON oauth_account (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN oauth_account.user_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_card (id UUID NOT NULL, owner_id UUID NOT NULL, card_template_id UUID NOT NULL, obtained_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, obtained_from VARCHAR(50) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6C95D41A7E3C61F9 ON user_card (owner_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6C95D41AE20E5022 ON user_card (card_template_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN user_card.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN user_card.owner_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN user_card.card_template_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN user_card.obtained_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, profile_picture VARCHAR(100) NOT NULL, username VARCHAR(120) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON users (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON users (username)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users.id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card ADD CONSTRAINT FK_161498D3514956FD FOREIGN KEY (collection_id) REFERENCES card_collection (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_collection ADD CONSTRAINT FK_903FF83C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_account ADD CONSTRAINT FK_6E30F9D1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card ADD CONSTRAINT FK_6C95D41A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card ADD CONSTRAINT FK_6C95D41AE20E5022 FOREIGN KEY (card_template_id) REFERENCES card (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card DROP CONSTRAINT FK_161498D3514956FD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_collection DROP CONSTRAINT FK_903FF83C7E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_account DROP CONSTRAINT FK_6E30F9D1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card DROP CONSTRAINT FK_6C95D41A7E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_card DROP CONSTRAINT FK_6C95D41AE20E5022
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
            DROP TABLE user_card
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
    }
}
