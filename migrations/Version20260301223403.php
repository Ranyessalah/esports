<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301223403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fixture ADD match_link VARCHAR(255) DEFAULT NULL, CHANGE round round VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE league ADD description LONGTEXT DEFAULT NULL, ADD location VARCHAR(255) DEFAULT NULL, ADD max_teams INT DEFAULT NULL, ADD banner VARCHAR(255) DEFAULT NULL, CHANGE prize_pool prize_pool DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fixture DROP match_link, CHANGE round round VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE league DROP description, DROP location, DROP max_teams, DROP banner, CHANGE prize_pool prize_pool DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
