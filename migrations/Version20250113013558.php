<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250113013558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Créer la table wishlist
        $this->addSql('CREATE TABLE wishlist (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(id))');
        
        // Créer la table d'association wishlist_products (relation Many-to-Many entre Wishlist et Product)
        $this->addSql('CREATE TABLE wishlist_products (wishlist_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_WISHLIST_PRODUCTS_WISHLIST_ID (wishlist_id), INDEX IDX_WISHLIST_PRODUCTS_PRODUCT_ID (product_id), PRIMARY KEY(wishlist_id, product_id))');
        $this->addSql('ALTER TABLE wishlist_products ADD CONSTRAINT FK_WISHLIST_PRODUCTS_WISHLIST_ID FOREIGN KEY (wishlist_id) REFERENCES wishlist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wishlist_products ADD CONSTRAINT FK_WISHLIST_PRODUCTS_PRODUCT_ID FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la table d'association wishlist_products
        $this->addSql('DROP TABLE wishlist_products');

        // Supprimer la table wishlist
        $this->addSql('DROP TABLE wishlist');

    }
}
