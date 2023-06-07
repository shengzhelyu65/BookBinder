<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230607190808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book (id INT AUTO_INCREMENT NOT NULL, google_books_id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, thumbnail VARCHAR(600) NOT NULL, rating SMALLINT NOT NULL, review_count INT NOT NULL, author VARCHAR(255) NOT NULL, pages INT NOT NULL, published_date DATE NOT NULL, category VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_reviews (review_id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, book_id VARCHAR(255) NOT NULL, rating SMALLINT DEFAULT NULL, tags LONGTEXT DEFAULT NULL, review LONGTEXT NOT NULL, created_at DATETIME NOT NULL, book_title VARCHAR(200) NOT NULL, INDEX IDX_FA50C399A76ED395 (user_id), PRIMARY KEY(review_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE library (library_id INT AUTO_INCREMENT NOT NULL, library_name VARCHAR(50) NOT NULL, zip_code INT NOT NULL, city VARCHAR(25) NOT NULL, house_number INT NOT NULL, street VARCHAR(250) NOT NULL, number VARCHAR(25) NOT NULL, website VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(library_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meetup_list (meetup_list_id INT AUTO_INCREMENT NOT NULL, meetup_ID INT NOT NULL, user_ID INT NOT NULL, INDEX IDX_935A7335F7F4277C (meetup_ID), INDEX IDX_935A7335984D7FF (user_ID), PRIMARY KEY(meetup_list_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meetup_request_list (meetup_request_list_id INT AUTO_INCREMENT NOT NULL, meetup_ID INT NOT NULL, user_ID INT NOT NULL, INDEX IDX_B876026CF7F4277C (meetup_ID), INDEX IDX_B876026C984D7FF (user_ID), PRIMARY KEY(meetup_request_list_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meetup_requests (meetup_id INT AUTO_INCREMENT NOT NULL, host_user_id INT NOT NULL, book_id VARCHAR(255) NOT NULL, max_number INT NOT NULL, datetime DATETIME NOT NULL, library_ID INT NOT NULL, INDEX IDX_9F7DFA3C50CF45BD (library_ID), INDEX IDX_9F7DFA3C9092FFA4 (host_user_id), PRIMARY KEY(meetup_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_personal_info (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, surname VARCHAR(255) DEFAULT NULL, nickname VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_140D9B3AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reading_interest (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, languages JSON NOT NULL, genres JSON NOT NULL, UNIQUE INDEX UNIQ_47DC050AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reading_list (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, currently_reading JSON NOT NULL, want_to_read JSON NOT NULL, have_read JSON NOT NULL, UNIQUE INDEX UNIQ_F352EC4EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE book_reviews ADD CONSTRAINT FK_FA50C399A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meetup_list ADD CONSTRAINT FK_935A7335F7F4277C FOREIGN KEY (meetup_ID) REFERENCES meetup_requests (meetup_id)');
        $this->addSql('ALTER TABLE meetup_list ADD CONSTRAINT FK_935A7335984D7FF FOREIGN KEY (user_ID) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meetup_request_list ADD CONSTRAINT FK_B876026CF7F4277C FOREIGN KEY (meetup_ID) REFERENCES meetup_requests (meetup_id)');
        $this->addSql('ALTER TABLE meetup_request_list ADD CONSTRAINT FK_B876026C984D7FF FOREIGN KEY (user_ID) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meetup_requests ADD CONSTRAINT FK_9F7DFA3C50CF45BD FOREIGN KEY (library_ID) REFERENCES library (library_id)');
        $this->addSql('ALTER TABLE meetup_requests ADD CONSTRAINT FK_9F7DFA3C9092FFA4 FOREIGN KEY (host_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_personal_info ADD CONSTRAINT FK_140D9B3AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_reading_interest ADD CONSTRAINT FK_47DC050AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_reading_list ADD CONSTRAINT FK_F352EC4EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_reviews DROP FOREIGN KEY FK_FA50C399A76ED395');
        $this->addSql('ALTER TABLE meetup_list DROP FOREIGN KEY FK_935A7335F7F4277C');
        $this->addSql('ALTER TABLE meetup_list DROP FOREIGN KEY FK_935A7335984D7FF');
        $this->addSql('ALTER TABLE meetup_request_list DROP FOREIGN KEY FK_B876026CF7F4277C');
        $this->addSql('ALTER TABLE meetup_request_list DROP FOREIGN KEY FK_B876026C984D7FF');
        $this->addSql('ALTER TABLE meetup_requests DROP FOREIGN KEY FK_9F7DFA3C50CF45BD');
        $this->addSql('ALTER TABLE meetup_requests DROP FOREIGN KEY FK_9F7DFA3C9092FFA4');
        $this->addSql('ALTER TABLE user_personal_info DROP FOREIGN KEY FK_140D9B3AA76ED395');
        $this->addSql('ALTER TABLE user_reading_interest DROP FOREIGN KEY FK_47DC050AA76ED395');
        $this->addSql('ALTER TABLE user_reading_list DROP FOREIGN KEY FK_F352EC4EA76ED395');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE book_reviews');
        $this->addSql('DROP TABLE library');
        $this->addSql('DROP TABLE meetup_list');
        $this->addSql('DROP TABLE meetup_request_list');
        $this->addSql('DROP TABLE meetup_requests');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_personal_info');
        $this->addSql('DROP TABLE user_reading_interest');
        $this->addSql('DROP TABLE user_reading_list');
    }
}
