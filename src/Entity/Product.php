<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Category $category = null;

    #[ORM\Column(name: 'product_name', length: 150)]
    private ?string $productName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = '0.00';

    #[ORM\Column(type: Types::INTEGER)]
    private int $quantity = 0;

    #[ORM\Column(name: 'minimum_stock', type: Types::INTEGER)]
    private int $minimumStock = 10;

    #[ORM\Column(length: 30)]
    private string $unit = 'piece';

    #[ORM\Column(name: 'image_path', length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, StockMovement>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: StockMovement::class)]
    private Collection $stockMovements;

    public function __construct()
    {
        $this->stockMovements = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id ? (int)$this->id : null;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getCategoryName(): string
    {
        return $this->category ? (string)$this->category->getCategoryName() : 'General';
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): static
    {
        $this->productName = trim($productName);
        return $this;
    }

    // Name alias for templates
    public function getName(): ?string
    {
        return $this->productName;
    }

    public function setName(string $name): static
    {
        return $this->setProductName($name);
    }

    // SKU helper
    public function getSku(): string
    {
        $prefix = strtoupper(substr($this->getCategoryName(), 0, 3));
        return $prefix . '-' . str_pad((string)($this->id ?? 1), 3, '0', STR_PAD_LEFT);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string|float $price): static
    {
        $this->price = number_format((float)$price, 2, '.', '');
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = max(0, $quantity);
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getMinimumStock(): int
    {
        return $this->minimumStock;
    }

    public function setMinimumStock(int $minimumStock): static
    {
        $this->minimumStock = max(0, $minimumStock);
        return $this;
    }

    public function getMinStock(): int
    {
        return $this->minimumStock;
    }

    public function setMinStock(int $minStock): static
    {
        return $this->setMinimumStock($minStock);
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): static
    {
        $this->unit = $unit;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isLowStock(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->minimumStock;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function getStockStatus(): string
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }
        if ($this->quantity <= $this->minimumStock) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getStockStatusLabel(): string
    {
        if ($this->quantity <= 0) {
            return 'Out of Stock';
        }
        if ($this->quantity <= $this->minimumStock) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    /**
     * @return Collection<int, StockMovement>
     */
    public function getStockMovements(): Collection
    {
        return $this->stockMovements;
    }
}
