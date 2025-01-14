<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')] 
#[ORM\UniqueConstraint(name: 'unique_code', columns: ['code'])]
#[ORM\HasLifecycleCallbacks]  // Add attribut HasLifecycleCallbacks
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
     private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(message: "Not missing")]
    private $code;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Not missing")]
    #[Assert\Length(min: 3, max: 255, minMessage: "limit number of caract")]
    private $name;

    #[ORM\Column(type: 'text')]
    #[Assert\Length(min: 3, minMessage: "limit number of caract")]
    private $description;

    #[ORM\Column(type: 'string', length: 255)]
    private $image = null;

    #[ORM\Column(type: 'string', length: 255)]
    private $category = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "the price is required")]
    #[Assert\Positive(message: "The price must be a positive number.")]
    private $price;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "The quantity is required.")]
    #[Assert\PositiveOrZero(message: "The quantity cannot be negative.")]
    private $quantity;

    #[ORM\Column(type: 'string', length: 255)]
    private $internalReference;

    #[ORM\Column(type: 'integer')]
    private $shellId;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(message: "The inventory status is required.")]
    #[Assert\Choice(
        choices: ["INSTOCK", "LOWSTOCK", "OUTOFSTOCK"],
        message: "The inventory status must be one of INSTOCK, LOWSTOCK, or OUTOFSTOCK."
    )]
    private $inventoryStatus;

    #[ORM\Column(type: 'integer')]
    private $rating;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    // Getters 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function getImage(): ?string
    {
        return $this->image;
    }
    public function getCategory(): ?string
    {
        return $this->category;
    }
    public function getPrice(): ?string
    {
        return $this->price;
    }
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }
    public function getInternalReference(): ?string
    {
        return $this->internalReference;
    }
    public function getShellId(): ?int
    {
        return $this->shellId;
    }
    public function getInventoryStatus(): ?string
    {
      return $this->inventoryStatus;
    }
    public function getRating(): int
    {
      return $this->rating;
    }
    // Setters
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }
    public function setPrice(?int $price): self
    {
        $this->price = $price; 

        return $this;
    }
    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
    public function setInternalReference(?string $internalReference): self
    {
        $this->internalReference = $internalReference;

        return $this;
    }
    public function setShellId(?int $shellId): self
    {
        $this->shellId = $shellId;

        return $this;
    }
    public function setInventoryStatus(?string $inventoryStatus): self
    {
        $this->inventoryStatus = $inventoryStatus;

        return $this;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
