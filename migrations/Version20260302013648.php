<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302013648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coach (specialite VARCHAR(100) NOT NULL, disponibilite TINYINT NOT NULL, pays VARCHAR(100) NOT NULL, id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE equipe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, logo VARCHAR(255) NOT NULL, game VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, coach_id INT NOT NULL, INDEX IDX_2449BA153C105691 (coach_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE fixture (id INT AUTO_INCREMENT NOT NULL, match_date DATETIME NOT NULL, score_team1 INT DEFAULT NULL, score_team2 INT DEFAULT NULL, status VARCHAR(50) NOT NULL, round VARCHAR(50) DEFAULT NULL, match_link VARCHAR(255) DEFAULT NULL, league_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_5E540EE58AFC4DE (league_id), INDEX IDX_5E540EEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE league (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, game VARCHAR(100) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, num_teams INT NOT NULL, format VARCHAR(100) NOT NULL, status VARCHAR(255) NOT NULL, prize_pool DOUBLE PRECISION DEFAULT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, max_teams INT DEFAULT NULL, banner VARCHAR(255) DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_3EB4C318A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE matchs (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(30) NOT NULL, date_match DATETIME NOT NULL, date_fin_match DATETIME NOT NULL, score_equipe1 INT DEFAULT NULL, score_equipe2 INT DEFAULT NULL, nom_match VARCHAR(255) NOT NULL, equipe1_id INT NOT NULL, equipe2_id INT NOT NULL, INDEX IDX_6B1E60414265900C (equipe1_id), INDEX IDX_6B1E604150D03FE2 (equipe2_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE player (pays VARCHAR(100) NOT NULL, statut TINYINT NOT NULL, niveau VARCHAR(20) DEFAULT NULL, equipe_id INT DEFAULT NULL, id INT NOT NULL, INDEX IDX_98197A656D861B89 (equipe_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, google_id VARCHAR(255) DEFAULT NULL, is_blocked TINYINT DEFAULT 0 NOT NULL, profile_image VARCHAR(255) DEFAULT NULL, totp_secret VARCHAR(255) DEFAULT NULL, is_totp_enabled TINYINT DEFAULT 0 NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCCBF396750 FOREIGN KEY (id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA153C105691 FOREIGN KEY (coach_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE fixture ADD CONSTRAINT FK_5E540EE58AFC4DE FOREIGN KEY (league_id) REFERENCES league (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fixture ADD CONSTRAINT FK_5E540EEA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE league ADD CONSTRAINT FK_3EB4C318A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE matchs ADD CONSTRAINT FK_6B1E60414265900C FOREIGN KEY (equipe1_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE matchs ADD CONSTRAINT FK_6B1E604150D03FE2 FOREIGN KEY (equipe2_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A656D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65BF396750 FOREIGN KEY (id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY FK_3F596DCCBF396750');
        $this->addSql('ALTER TABLE equipe DROP FOREIGN KEY FK_2449BA153C105691');
        $this->addSql('ALTER TABLE fixture DROP FOREIGN KEY FK_5E540EE58AFC4DE');
        $this->addSql('ALTER TABLE fixture DROP FOREIGN KEY FK_5E540EEA76ED395');
        $this->addSql('ALTER TABLE league DROP FOREIGN KEY FK_3EB4C318A76ED395');
        $this->addSql('ALTER TABLE matchs DROP FOREIGN KEY FK_6B1E60414265900C');
        $this->addSql('ALTER TABLE matchs DROP FOREIGN KEY FK_6B1E604150D03FE2');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A656D861B89');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65BF396750');
        $this->addSql('DROP TABLE coach');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE fixture');
        $this->addSql('DROP TABLE league');
        $this->addSql('DROP TABLE matchs');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE `user`');
    }
}
