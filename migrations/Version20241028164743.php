<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241028164743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pv_contact_topic (contact_id INT NOT NULL, topic_id INT NOT NULL, INDEX IDX_D9FA01EFE7A1254A (contact_id), INDEX IDX_D9FA01EF1F55203D (topic_id), PRIMARY KEY(contact_id, topic_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pv_contact_topic ADD CONSTRAINT FK_D9FA01EFE7A1254A FOREIGN KEY (contact_id) REFERENCES pv_contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_contact_topic ADD CONSTRAINT FK_D9FA01EF1F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pv_contact ADD one_time_code VARCHAR(255) DEFAULT NULL, ADD verification_email_sent_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_contact_topic DROP FOREIGN KEY FK_D9FA01EFE7A1254A');
        $this->addSql('ALTER TABLE pv_contact_topic DROP FOREIGN KEY FK_D9FA01EF1F55203D');
        $this->addSql('DROP TABLE pv_contact_topic');
        $this->addSql('ALTER TABLE pv_contact DROP one_time_code, DROP verification_email_sent_date');
    }
}
