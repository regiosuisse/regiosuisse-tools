<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715093748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_publication (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, title LONGTEXT DEFAULT NULL, keywords LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, email LONGTEXT DEFAULT NULL, type VARCHAR(128) DEFAULT NULL, url LONGTEXT DEFAULT NULL, license_type VARCHAR(128) DEFAULT NULL, license_name VARCHAR(128) DEFAULT NULL, license_url VARCHAR(128) DEFAULT NULL, license_attribution LONGTEXT DEFAULT NULL, rights_holder VARCHAR(128) DEFAULT NULL, authors LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', organizations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_publication_topic (publication_id INT NOT NULL, topic_id INT NOT NULL, INDEX IDX_F6258BAA38B217A7 (publication_id), INDEX IDX_F6258BAA1F55203D (topic_id), PRIMARY KEY(publication_id, topic_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_publication_geographic_region (publication_id INT NOT NULL, geographic_region_id INT NOT NULL, INDEX IDX_B9A83BAD38B217A7 (publication_id), INDEX IDX_B9A83BAD81314161 (geographic_region_id), PRIMARY KEY(publication_id, geographic_region_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_publication_language (publication_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_EA53D93F38B217A7 (publication_id), INDEX IDX_EA53D93F82F1BAF4 (language_id), PRIMARY KEY(publication_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_publication_topic ADD CONSTRAINT FK_F6258BAA38B217A7 FOREIGN KEY (publication_id) REFERENCES pv_publication (id)');
        $this->addSql('ALTER TABLE pv_publication_topic ADD CONSTRAINT FK_F6258BAA1F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
        $this->addSql('ALTER TABLE pv_publication_geographic_region ADD CONSTRAINT FK_B9A83BAD38B217A7 FOREIGN KEY (publication_id) REFERENCES pv_publication (id)');
        $this->addSql('ALTER TABLE pv_publication_geographic_region ADD CONSTRAINT FK_B9A83BAD81314161 FOREIGN KEY (geographic_region_id) REFERENCES pv_geographic_region (id)');
        $this->addSql('ALTER TABLE pv_publication_language ADD CONSTRAINT FK_EA53D93F38B217A7 FOREIGN KEY (publication_id) REFERENCES pv_publication (id)');
        $this->addSql('ALTER TABLE pv_publication_language ADD CONSTRAINT FK_EA53D93F82F1BAF4 FOREIGN KEY (language_id) REFERENCES pv_language (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_publication_topic DROP FOREIGN KEY FK_F6258BAA38B217A7');
        $this->addSql('ALTER TABLE pv_publication_topic DROP FOREIGN KEY FK_F6258BAA1F55203D');
        $this->addSql('ALTER TABLE pv_publication_geographic_region DROP FOREIGN KEY FK_B9A83BAD38B217A7');
        $this->addSql('ALTER TABLE pv_publication_geographic_region DROP FOREIGN KEY FK_B9A83BAD81314161');
        $this->addSql('ALTER TABLE pv_publication_language DROP FOREIGN KEY FK_EA53D93F38B217A7');
        $this->addSql('ALTER TABLE pv_publication_language DROP FOREIGN KEY FK_EA53D93F82F1BAF4');
        $this->addSql('DROP TABLE pv_publication');
        $this->addSql('DROP TABLE pv_publication_topic');
        $this->addSql('DROP TABLE pv_publication_geographic_region');
        $this->addSql('DROP TABLE pv_publication_language');
    }
}
