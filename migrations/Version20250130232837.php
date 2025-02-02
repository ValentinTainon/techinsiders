<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250130232837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_64C19C1DF2B4115 ON category');
        $this->addSql('ALTER TABLE category CHANGE name_slug slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C1989D9B62 ON category (slug)');
        $this->addSql('DROP INDEX UNIQ_5A8A6C8DD347411D ON post');
        $this->addSql('ALTER TABLE post CHANGE title_slug slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8D989D9B62 ON post (slug)');
        $this->addSql('DROP INDEX UNIQ_389B783DF2B4115 ON tag');
        $this->addSql('ALTER TABLE tag CHANGE name_slug slug VARCHAR(30) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B783989D9B62 ON tag (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_64C19C1989D9B62 ON category');
        $this->addSql('ALTER TABLE category CHANGE slug name_slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C1DF2B4115 ON category (name_slug)');
        $this->addSql('DROP INDEX UNIQ_5A8A6C8D989D9B62 ON post');
        $this->addSql('ALTER TABLE post CHANGE slug title_slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8DD347411D ON post (title_slug)');
        $this->addSql('DROP INDEX UNIQ_389B783989D9B62 ON tag');
        $this->addSql('ALTER TABLE tag CHANGE slug name_slug VARCHAR(30) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B783DF2B4115 ON tag (name_slug)');
    }
}
