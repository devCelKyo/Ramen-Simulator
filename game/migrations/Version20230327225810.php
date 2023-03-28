<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230327225810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE restaurant ADD stars INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD restaurant_slots INT DEFAULT NULL');
        $this->addSql('UPDATE user SET restaurant_slots = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE restaurant DROP stars');
        $this->addSql('ALTER TABLE user DROP restaurant_slots');
    }
}
