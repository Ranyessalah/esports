<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206233823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, logo VARCHAR(255) NOT NULL, game VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, coach_id INT NOT NULL, INDEX IDX_2449BA153C105691 (coach_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE equipe_user (equipe_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_84DA47B76D861B89 (equipe_id), INDEX IDX_84DA47B7A76ED395 (user_id), PRIMARY KEY(equipe_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE matchs (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(255) NOT NULL, date_match DATETIME NOT NULL, date_fin_match DATETIME NOT NULL, score_equipe1 INT DEFAULT NULL, score_equipe2 INT DEFAULT NULL, equipe1_id INT NOT NULL, equipe2_id INT NOT NULL, INDEX IDX_6B1E60414265900C (equipe1_id), INDEX IDX_6B1E604150D03FE2 (equipe2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA153C105691 FOREIGN KEY (coach_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE equipe_user ADD CONSTRAINT FK_84DA47B76D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe_user ADD CONSTRAINT FK_84DA47B7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE matchs ADD CONSTRAINT FK_6B1E60414265900C FOREIGN KEY (equipe1_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE matchs ADD CONSTRAINT FK_6B1E604150D03FE2 FOREIGN KEY (equipe2_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipe DROP FOREIGN KEY FK_2449BA153C105691');
        $this->addSql('ALTER TABLE equipe_user DROP FOREIGN KEY FK_84DA47B76D861B89');
        $this->addSql('ALTER TABLE equipe_user DROP FOREIGN KEY FK_84DA47B7A76ED395');
        $this->addSql('ALTER TABLE matchs DROP FOREIGN KEY FK_6B1E60414265900C');
        $this->addSql('ALTER TABLE matchs DROP FOREIGN KEY FK_6B1E604150D03FE2');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE equipe_user');
        $this->addSql('DROP TABLE matchs');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
