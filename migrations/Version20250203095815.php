<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203095815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD chat_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497E3973CC FOREIGN KEY (chat_id_id) REFERENCES chat_room (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6497E3973CC ON user (chat_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497E3973CC');
        $this->addSql('DROP INDEX IDX_8D93D6497E3973CC ON user');
        $this->addSql('ALTER TABLE user DROP chat_id_id');
    }
}
