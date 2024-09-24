<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240924185837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_user (id UUID NOT NULL, name VARCHAR(255) NOT NULL, auth_token VARCHAR(255) NOT NULL, is_admin BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN api_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE result (id UUID NOT NULL, task_id UUID NOT NULL, content BYTEA NOT NULL, mime_type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_136AC1138DB60186 ON result (task_id)');
        $this->addSql('COMMENT ON COLUMN result.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN result.task_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE task (id UUID NOT NULL, user_id UUID DEFAULT NULL, key VARCHAR(255) NOT NULL, params JSON NOT NULL, reserved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_527EDB258A90ABA9 ON task (key)');
        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
        $this->addSql('COMMENT ON COLUMN task.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN task.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC1138DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id) REFERENCES api_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE result DROP CONSTRAINT FK_136AC1138DB60186');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25A76ED395');
        $this->addSql('DROP TABLE api_user');
        $this->addSql('DROP TABLE result');
        $this->addSql('DROP TABLE task');
    }
}
