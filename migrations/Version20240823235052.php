<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240823235052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post RENAME INDEX idx_5a8a6c8df675f31b TO IDX_5A8A6C8DA76ED395');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EMAIL ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post RENAME INDEX idx_5a8a6c8da76ed395 TO IDX_5A8A6C8DF675F31B');
        $this->addSql('DROP INDEX UNIQ_EMAIL ON user');
    }
}
