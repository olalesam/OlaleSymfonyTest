<?php

namespace App\Entity;

use App\Repository\UserRepository;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]

#[ORM\Table(name: 'users')] 
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: "email is required")]
    private ?string $email = null;
    
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "username is required")]
    private ?string $username = null;

    #[ORM\Column(type: 'string', name: 'first_name')]
    private ?string $firstName = null;  

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: "password is required.")]
    private ?string $password = null;

    #[ORM\OneToMany(targetEntity: Cart::class, mappedBy: 'user')]
    private Collection $carts;

    #[ORM\OneToMany(targetEntity: Wishlist::class, mappedBy: 'user')]
    private Collection $wishlists;

    public function __construct()
    {
        $this->carts = new ArrayCollection();
        $this->wishlists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }    
    public function getFirstName(): ?string
    {
        return $this->firstName;  
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;  

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUserName(?string $username): self
    {
        $this->username = $username;  

        return $this;
    }



    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {

        return [];
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    // Getter for Carts
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    // add Cart
    public function addCart(Cart $cart): void
    {
        if (!$this->carts->contains($cart)) {
            $this->carts->add($cart);
            $cart->setUser($this);
        }
    }

    // remove Cart
    public function removeCart(Cart $cart): void
    {
        if ($this->carts->removeElement($cart)) {
            if ($cart->getUser() === $this) {
                $cart->setUser(null);
            }
        }
    }

    // Getter for Wishlists
    public function getWishlists(): Collection
    {
        return $this->wishlists;
    }

    // Add Wishlist
    public function addWishlist(Wishlist $wishlist): void
    {
        if (!$this->wishlists->contains($wishlist)) {
            $this->wishlists->add($wishlist);
            $wishlist->setUser($this);
        }
    }

    // remove un Wishlist
    public function removeWishlist(Wishlist $wishlist): void
    {
        if ($this->wishlists->removeElement($wishlist)) {
            if ($wishlist->getUser() === $this) {
                $wishlist->setUser(null);
            }
        }
    }
    
}