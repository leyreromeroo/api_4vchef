<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104132824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ingrediente (id INT AUTO_INCREMENT NOT NULL, receta_id INT NOT NULL, nombre VARCHAR(30) NOT NULL, cantidad DOUBLE PRECISION NOT NULL, unidad VARCHAR(50) NOT NULL, INDEX IDX_BFB4A41E54F853F8 (receta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paso (id INT AUTO_INCREMENT NOT NULL, receta_id INT NOT NULL, orden INT NOT NULL, descripcion LONGTEXT NOT NULL, INDEX IDX_DA71886B54F853F8 (receta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ingrediente ADD CONSTRAINT FK_BFB4A41E54F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id)');
        $this->addSql('ALTER TABLE paso ADD CONSTRAINT FK_DA71886B54F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingrediente DROP FOREIGN KEY FK_BFB4A41E54F853F8');
        $this->addSql('ALTER TABLE paso DROP FOREIGN KEY FK_DA71886B54F853F8');
        $this->addSql('DROP TABLE ingrediente');
        $this->addSql('DROP TABLE paso');
    }
}
