<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104133736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE receta_nutriente (id INT AUTO_INCREMENT NOT NULL, receta_id INT NOT NULL, nutriente_id INT NOT NULL, cantidad DOUBLE PRECISION NOT NULL, INDEX IDX_5A698B7C54F853F8 (receta_id), INDEX IDX_5A698B7CA94AA29D (nutriente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE valoracion (id INT AUTO_INCREMENT NOT NULL, receta_id INT NOT NULL, puntuacion INT NOT NULL, ip VARCHAR(45) NOT NULL, INDEX IDX_6D3DE0F454F853F8 (receta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE receta_nutriente ADD CONSTRAINT FK_5A698B7C54F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id)');
        $this->addSql('ALTER TABLE receta_nutriente ADD CONSTRAINT FK_5A698B7CA94AA29D FOREIGN KEY (nutriente_id) REFERENCES tipo_nutriente (id)');
        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_6D3DE0F454F853F8 FOREIGN KEY (receta_id) REFERENCES receta (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE receta_nutriente DROP FOREIGN KEY FK_5A698B7C54F853F8');
        $this->addSql('ALTER TABLE receta_nutriente DROP FOREIGN KEY FK_5A698B7CA94AA29D');
        $this->addSql('ALTER TABLE valoracion DROP FOREIGN KEY FK_6D3DE0F454F853F8');
        $this->addSql('DROP TABLE receta_nutriente');
        $this->addSql('DROP TABLE valoracion');
    }
}
