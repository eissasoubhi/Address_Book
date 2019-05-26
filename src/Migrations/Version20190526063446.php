<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190526063446 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__address AS SELECT id, firstname, lastname, picture, streetnumber, zip, city, country, phonenumber, email, birthday FROM address');
        $this->addSql('DROP TABLE address');
        $this->addSql('CREATE TABLE address (id INTEGER NOT NULL, firstname VARCHAR(255) NOT NULL COLLATE BINARY, lastname VARCHAR(255) NOT NULL COLLATE BINARY, picture VARCHAR(255) DEFAULT NULL COLLATE BINARY, zip INTEGER NOT NULL, city VARCHAR(255) NOT NULL COLLATE BINARY, country VARCHAR(255) NOT NULL COLLATE BINARY, phonenumber VARCHAR(255) NOT NULL COLLATE BINARY, email VARCHAR(255) NOT NULL COLLATE BINARY, birthday VARCHAR(255) NOT NULL COLLATE BINARY, streetnumber VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO address (id, firstname, lastname, picture, streetnumber, zip, city, country, phonenumber, email, birthday) SELECT id, firstname, lastname, picture, streetnumber, zip, city, country, phonenumber, email, birthday FROM __temp__address');
        $this->addSql('DROP TABLE __temp__address');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__address AS SELECT id, firstname, lastname, picture, streetnumber, zip, city, country, phonenumber, birthday, email FROM address');
        $this->addSql('DROP TABLE address');
        $this->addSql('CREATE TABLE address (id INTEGER NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, picture VARCHAR(255) DEFAULT NULL, zip INTEGER NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, phonenumber VARCHAR(255) NOT NULL, birthday VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, streetnumber INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO address (id, firstname, lastname, picture, streetnumber, zip, city, country, phonenumber, birthday, email) SELECT id, firstname, lastname, picture, streetnumber, zip, city, country, phonenumber, birthday, email FROM __temp__address');
        $this->addSql('DROP TABLE __temp__address');
    }
}
