<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230519120740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE library (library_id INT AUTO_INCREMENT NOT NULL, library_name VARCHAR(50) NOT NULL, zip_code INT NOT NULL, city VARCHAR(25) NOT NULL, house_number INT NOT NULL, number VARCHAR(25) NOT NULL, website VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(library_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meetup_request_list (meetup_list_id INT AUTO_INCREMENT NOT NULL, meetup_ID INT NOT NULL, user_ID INT NOT NULL, INDEX IDX_B876026CF7F4277C (meetup_ID), INDEX IDX_B876026C984D7FF (user_ID), PRIMARY KEY(meetup_list_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meetup_requests (meetup_id INT AUTO_INCREMENT NOT NULL, host_user_id INT NOT NULL, book_id INT NOT NULL, max_number INT NOT NULL, library_ID INT NOT NULL, INDEX IDX_9F7DFA3C50CF45BD (library_ID), INDEX IDX_9F7DFA3C9092FFA4 (host_user_id), PRIMARY KEY(meetup_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meetup_request_list ADD CONSTRAINT FK_B876026CF7F4277C FOREIGN KEY (meetup_ID) REFERENCES meetup_requests (meetup_ID)');
        $this->addSql('ALTER TABLE meetup_request_list ADD CONSTRAINT FK_B876026C984D7FF FOREIGN KEY (user_ID) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meetup_requests ADD CONSTRAINT FK_9F7DFA3C50CF45BD FOREIGN KEY (library_ID) REFERENCES library (library_ID)');
        $this->addSql('ALTER TABLE meetup_requests ADD CONSTRAINT FK_9F7DFA3C9092FFA4 FOREIGN KEY (host_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE book_reviews ADD created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meetup_request_list DROP FOREIGN KEY FK_B876026CF7F4277C');
        $this->addSql('ALTER TABLE meetup_request_list DROP FOREIGN KEY FK_B876026C984D7FF');
        $this->addSql('ALTER TABLE meetup_requests DROP FOREIGN KEY FK_9F7DFA3C50CF45BD');
        $this->addSql('ALTER TABLE meetup_requests DROP FOREIGN KEY FK_9F7DFA3C9092FFA4');
        $this->addSql('DROP TABLE library');
        $this->addSql('DROP TABLE meetup_request_list');
        $this->addSql('DROP TABLE meetup_requests');
        $this->addSql('ALTER TABLE book_reviews DROP created_at');
    }
}
