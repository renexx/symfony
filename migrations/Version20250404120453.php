<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250404120453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Aktivovať UUID rozšírenie
        $this->addSql('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');

        // Odstrániť foreign key constraint
        $this->addSql('ALTER TABLE project DROP CONSTRAINT fk_2fb3d0ee7e3c61f9');

        // Odstrániť sekvencie
        $this->addSql('DROP SEQUENCE IF EXISTS project_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS user_id_seq CASCADE');

        // Zmeniť id v "user" na UUID
        $this->addSql('ALTER TABLE "user" ALTER COLUMN id SET DATA TYPE UUID USING uuid_generate_v4()');

        // Zmeniť owner_id v "project" na UUID
        $this->addSql('ALTER TABLE project ALTER COLUMN owner_id SET DATA TYPE UUID USING uuid_generate_v4()');

        // Zmeniť id v "project" na UUID
        $this->addSql('ALTER TABLE project ALTER COLUMN id SET DATA TYPE UUID USING uuid_generate_v4()');

        // Znovu pridať foreign key constraint
        $this->addSql('ALTER TABLE project ADD CONSTRAINT fk_2fb3d0ee7e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Odstrániť foreign key constraint
        $this->addSql('ALTER TABLE project DROP CONSTRAINT fk_2fb3d0ee7e3c61f9');

        // Vytvoriť sekvencie späť
        $this->addSql('CREATE SEQUENCE project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        // Vrátiť id v "user" na INT
        $this->addSql('ALTER TABLE "user" ALTER COLUMN id SET DATA TYPE INT USING id::integer');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN id SET DEFAULT nextval(\'user_id_seq\')');

        // Vrátiť owner_id v "project" na INT
        $this->addSql('ALTER TABLE project ALTER COLUMN owner_id SET DATA TYPE INT USING owner_id::integer');

        // Vrátiť id v "project" na INT
        $this->addSql('ALTER TABLE project ALTER COLUMN id SET DATA TYPE INT USING id::integer');
        $this->addSql('ALTER TABLE project ALTER COLUMN id SET DEFAULT nextval(\'project_id_seq\')');

        // Znovu pridať foreign key constraint
        $this->addSql('ALTER TABLE project ADD CONSTRAINT fk_2fb3d0ee7e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE');
    }
}
