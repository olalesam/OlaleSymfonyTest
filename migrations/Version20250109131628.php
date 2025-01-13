<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250109131628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, 
                                                code VARCHAR(100) NOT NULL, 
                                                name VARCHAR(255) NOT NULL, 
                                                description TEXT, 
                                                image VARCHAR(255), 
                                                category VARCHAR(100) NOT NULL, 
                                                price DECIMAL(10, 2) NOT NULL, 
                                                quantity INT NOT NULL, 
                                                internal_reference VARCHAR(255), 
                                                shell_id INT, 
                                                inventory_Status VARCHAR(20) NOT NULL, 
                                                rating INT, 
                                                created_at DATETIME, 
                                                updated_at DATETIME, 
                                                PRIMARY KEY(id), 
                                                UNIQUE(code))'
                                            );

    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE products');

    }
}
