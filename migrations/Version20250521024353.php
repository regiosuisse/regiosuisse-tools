<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521024353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_circular_economy_project (id INT AUTO_INCREMENT NOT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, title LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, keywords LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, links LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', videos LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', images LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', files LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', translations LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_circular_economy_project_topic (circular_economy_project_id INT NOT NULL, topic_id INT NOT NULL, INDEX IDX_C1E12A17DCEE8A4B (circular_economy_project_id), INDEX IDX_C1E12A171F55203D (topic_id), PRIMARY KEY(circular_economy_project_id, topic_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_circular_economy_project_geographic_region (circular_economy_project_id INT NOT NULL, geographic_region_id INT NOT NULL, INDEX IDX_581E05EFDCEE8A4B (circular_economy_project_id), INDEX IDX_581E05EF81314161 (geographic_region_id), PRIMARY KEY(circular_economy_project_id, geographic_region_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_circular_economy_project_country (circular_economy_project_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_4516ECCEDCEE8A4B (circular_economy_project_id), INDEX IDX_4516ECCEF92F3E70 (country_id), PRIMARY KEY(circular_economy_project_id, country_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_circular_economy_project_state (circular_economy_project_id INT NOT NULL, state_id INT NOT NULL, INDEX IDX_FF3226F7DCEE8A4B (circular_economy_project_id), INDEX IDX_FF3226F75D83CC1 (state_id), PRIMARY KEY(circular_economy_project_id, state_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_circular_economy_project_instrument (circular_economy_project_id INT NOT NULL, instrument_id INT NOT NULL, INDEX IDX_3EFF2023DCEE8A4B (circular_economy_project_id), INDEX IDX_3EFF2023CF11D9C (instrument_id), PRIMARY KEY(circular_economy_project_id, instrument_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pv_circular_economy_project_business_sector (circular_economy_project_id INT NOT NULL, business_sector_id INT NOT NULL, INDEX IDX_43B780DBDCEE8A4B (circular_economy_project_id), INDEX IDX_43B780DBC7F1CE18 (business_sector_id), PRIMARY KEY(circular_economy_project_id, business_sector_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_circular_economy_project_topic ADD CONSTRAINT FK_C1E12A17DCEE8A4B FOREIGN KEY (circular_economy_project_id) REFERENCES pv_circular_economy_project (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_topic ADD CONSTRAINT FK_C1E12A171F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_geographic_region ADD CONSTRAINT FK_581E05EFDCEE8A4B FOREIGN KEY (circular_economy_project_id) REFERENCES pv_circular_economy_project (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_geographic_region ADD CONSTRAINT FK_581E05EF81314161 FOREIGN KEY (geographic_region_id) REFERENCES pv_geographic_region (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_country ADD CONSTRAINT FK_4516ECCEDCEE8A4B FOREIGN KEY (circular_economy_project_id) REFERENCES pv_circular_economy_project (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_country ADD CONSTRAINT FK_4516ECCEF92F3E70 FOREIGN KEY (country_id) REFERENCES pv_country (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_state ADD CONSTRAINT FK_FF3226F7DCEE8A4B FOREIGN KEY (circular_economy_project_id) REFERENCES pv_circular_economy_project (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_state ADD CONSTRAINT FK_FF3226F75D83CC1 FOREIGN KEY (state_id) REFERENCES pv_state (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_instrument ADD CONSTRAINT FK_3EFF2023DCEE8A4B FOREIGN KEY (circular_economy_project_id) REFERENCES pv_circular_economy_project (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_instrument ADD CONSTRAINT FK_3EFF2023CF11D9C FOREIGN KEY (instrument_id) REFERENCES pv_instrument (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_business_sector ADD CONSTRAINT FK_43B780DBDCEE8A4B FOREIGN KEY (circular_economy_project_id) REFERENCES pv_circular_economy_project (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_business_sector ADD CONSTRAINT FK_43B780DBC7F1CE18 FOREIGN KEY (business_sector_id) REFERENCES pv_business_sector (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_circular_economy_project_topic DROP FOREIGN KEY FK_C1E12A17DCEE8A4B');
        $this->addSql('ALTER TABLE pv_circular_economy_project_topic DROP FOREIGN KEY FK_C1E12A171F55203D');
        $this->addSql('ALTER TABLE pv_circular_economy_project_geographic_region DROP FOREIGN KEY FK_581E05EFDCEE8A4B');
        $this->addSql('ALTER TABLE pv_circular_economy_project_geographic_region DROP FOREIGN KEY FK_581E05EF81314161');
        $this->addSql('ALTER TABLE pv_circular_economy_project_country DROP FOREIGN KEY FK_4516ECCEDCEE8A4B');
        $this->addSql('ALTER TABLE pv_circular_economy_project_country DROP FOREIGN KEY FK_4516ECCEF92F3E70');
        $this->addSql('ALTER TABLE pv_circular_economy_project_state DROP FOREIGN KEY FK_FF3226F7DCEE8A4B');
        $this->addSql('ALTER TABLE pv_circular_economy_project_state DROP FOREIGN KEY FK_FF3226F75D83CC1');
        $this->addSql('ALTER TABLE pv_circular_economy_project_instrument DROP FOREIGN KEY FK_3EFF2023DCEE8A4B');
        $this->addSql('ALTER TABLE pv_circular_economy_project_instrument DROP FOREIGN KEY FK_3EFF2023CF11D9C');
        $this->addSql('ALTER TABLE pv_circular_economy_project_business_sector DROP FOREIGN KEY FK_43B780DBDCEE8A4B');
        $this->addSql('ALTER TABLE pv_circular_economy_project_business_sector DROP FOREIGN KEY FK_43B780DBC7F1CE18');
        $this->addSql('DROP TABLE pv_circular_economy_project');
        $this->addSql('DROP TABLE pv_circular_economy_project_topic');
        $this->addSql('DROP TABLE pv_circular_economy_project_geographic_region');
        $this->addSql('DROP TABLE pv_circular_economy_project_country');
        $this->addSql('DROP TABLE pv_circular_economy_project_state');
        $this->addSql('DROP TABLE pv_circular_economy_project_instrument');
        $this->addSql('DROP TABLE pv_circular_economy_project_business_sector');
    }
}
