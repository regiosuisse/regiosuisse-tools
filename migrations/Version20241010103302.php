<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241010103302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_region_tag (region_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_5FF03EC98260155 (region_id), INDEX IDX_5FF03ECBAD26311 (tag_id), PRIMARY KEY(region_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_region_tag ADD CONSTRAINT FK_5FF03EC98260155 FOREIGN KEY (region_id) REFERENCES pv_region (id)');
        $this->addSql('ALTER TABLE pv_region_tag ADD CONSTRAINT FK_5FF03ECBAD26311 FOREIGN KEY (tag_id) REFERENCES pv_tag (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_region_tag DROP FOREIGN KEY FK_5FF03EC98260155');
        $this->addSql('ALTER TABLE pv_region_tag DROP FOREIGN KEY FK_5FF03ECBAD26311');
        $this->addSql('DROP TABLE pv_region_tag');
    }
}
