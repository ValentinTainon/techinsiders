<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241209220853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category RENAME INDEX uniq_name TO UNIQ_64C19C15E237E06');
        $this->addSql('ALTER TABLE post RENAME INDEX uniq_title TO UNIQ_5A8A6C8D2B36786B');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_identifier_username TO UNIQ_8D93D649F85E0677');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_email TO UNIQ_8D93D649E7927C74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category RENAME INDEX uniq_64c19c15e237e06 TO UNIQ_NAME');
        $this->addSql('ALTER TABLE post RENAME INDEX uniq_5a8a6c8d2b36786b TO UNIQ_TITLE');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649e7927c74 TO UNIQ_EMAIL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649f85e0677 TO UNIQ_IDENTIFIER_USERNAME');
    }
}
