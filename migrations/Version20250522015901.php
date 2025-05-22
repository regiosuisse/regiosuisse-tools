<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522015901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_circular_economy_project_tag (circular_economy_project_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_2A685CDADCEE8A4B (circular_economy_project_id), INDEX IDX_2A685CDABAD26311 (tag_id), PRIMARY KEY(circular_economy_project_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_circular_economy_project_tag ADD CONSTRAINT FK_2A685CDADCEE8A4B FOREIGN KEY (circular_economy_project_id) REFERENCES pv_circular_economy_project (id)');
        $this->addSql('ALTER TABLE pv_circular_economy_project_tag ADD CONSTRAINT FK_2A685CDABAD26311 FOREIGN KEY (tag_id) REFERENCES pv_tag (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_circular_economy_project_tag DROP FOREIGN KEY FK_2A685CDADCEE8A4B');
        $this->addSql('ALTER TABLE pv_circular_economy_project_tag DROP FOREIGN KEY FK_2A685CDABAD26311');
        $this->addSql('DROP TABLE pv_circular_economy_project_tag');
    }
}
