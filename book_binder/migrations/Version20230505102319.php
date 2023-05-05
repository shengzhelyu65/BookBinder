<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230505102319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_personal_info ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_personal_info ADD CONSTRAINT FK_140D9B3AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140D9B3AA76ED395 ON user_personal_info (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_personal_info DROP FOREIGN KEY FK_140D9B3AA76ED395');
        $this->addSql('DROP INDEX UNIQ_140D9B3AA76ED395 ON user_personal_info');
        $this->addSql('ALTER TABLE user_personal_info DROP user_id');
    }
}
