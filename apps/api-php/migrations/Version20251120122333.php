<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120122333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, datetime DATETIME NOT NULL, user_id INTEGER NOT NULL, provider_id INTEGER NOT NULL, service_id INTEGER NOT NULL, CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E00CEDDEA53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E00CEDDEED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA76ED395 ON booking (user_id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA53A8AA ON booking (provider_id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDEED5CA9E6 ON booking (service_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BOOKING_PROVIDER_DATETIME ON booking (provider_id, datetime)');
        $this->addSql('CREATE TABLE provider (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, working_hours CLOB NOT NULL)');
        $this->addSql('CREATE TABLE service (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, duration INTEGER NOT NULL)');
        $this->addSql('ALTER TABLE user ADD COLUMN deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE provider');
        $this->addSql('DROP TABLE service');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password) SELECT id, email, roles, password FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
