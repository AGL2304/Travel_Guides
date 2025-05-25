<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525171613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE visite ADD guide_id INT NOT NULL, ADD visite VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBBD7ED1D4B FOREIGN KEY (guide_id) REFERENCES guide (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B09C8CBBD7ED1D4B ON visite (guide_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visiteur ADD visite_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visiteur ADD CONSTRAINT FK_4EA587B8C1C5DC59 FOREIGN KEY (visite_id) REFERENCES visite (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4EA587B8C1C5DC59 ON visiteur (visite_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE visiteur DROP FOREIGN KEY FK_4EA587B8C1C5DC59
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4EA587B8C1C5DC59 ON visiteur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visiteur DROP visite_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBBD7ED1D4B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B09C8CBBD7ED1D4B ON visite
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visite DROP guide_id, DROP visite
        SQL);
    }
}
