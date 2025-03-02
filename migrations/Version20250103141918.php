<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250103141918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emotion ADD employe_id INT DEFAULT NULL, DROP confidence, CHANGE emotion emotion VARCHAR(255) NOT NULL, CHANGE timestamp date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE emotion ADD CONSTRAINT FK_DEBC771B65292 FOREIGN KEY (employe_id) REFERENCES employe (id)');
        $this->addSql('CREATE INDEX IDX_DEBC771B65292 ON emotion (employe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emotion DROP FOREIGN KEY FK_DEBC771B65292');
        $this->addSql('DROP INDEX IDX_DEBC771B65292 ON emotion');
        $this->addSql('ALTER TABLE emotion ADD confidence DOUBLE PRECISION NOT NULL, DROP employe_id, CHANGE emotion emotion VARCHAR(50) NOT NULL, CHANGE date timestamp DATETIME NOT NULL');
    }
}
