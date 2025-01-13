<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250113011700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Créer la table cart
        $this->addSql('CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, 
        user_id INT NOT NULL, 
        PRIMARY KEY(id))');
        
        // Créer la table d'association cart_products (relation Many-to-Many entre Cart et Product)
        $this->addSql('CREATE TABLE cart_products (cart_id INT NOT NULL,
         product_id INT NOT NULL, INDEX IDX_CART_PRODUCTS_CART_ID (cart_id), 
         INDEX IDX_CART_PRODUCTS_PRODUCT_ID (product_id), 
         PRIMARY KEY(cart_id, product_id))');
        $this->addSql('ALTER TABLE cart_products ADD CONSTRAINT FK_CART_PRODUCTS_CART_ID FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_products ADD CONSTRAINT FK_CART_PRODUCTS_PRODUCT_ID FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
