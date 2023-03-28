<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230325221110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE restaurant CHANGE ramen_stored ramen_stored LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', CHANGE workers workers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', CHANGE money_cached money_cached LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\'');
        $this->addSql('ALTER TABLE user DROP rebirth, CHANGE money money LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\'');
        $this->addSql('UPDATE user SET money = null');
        $this->addSql('TRUNCATE TABLE restaurant');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('TRUNCATE TABLE user');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE restaurant CHANGE ramen_stored ramen_stored INT NOT NULL, CHANGE workers workers INT NOT NULL, CHANGE money_cached money_cached INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD rebirth INT NOT NULL, CHANGE money money INT NOT NULL');
    }
}
