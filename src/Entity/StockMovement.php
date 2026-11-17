<?php

namespace App\Entity;

use App\Repository\StockMovementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockMovementRepository::class)]
#[ORM\Table(name: 'stock_movements')]
class StockMovement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'stockMovements')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(name: 'movement_type', length: 20)]
    private string $movementType = 'stock_in'; // stock_in, stock_out, sale, adjustment

    #[ORM\Column(type: Types::INTEGER)]
    private int $quantity = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarks = null;

    #[ORM\ManyToOne(targetEntity: Sale::class)]
    #[ORM\JoinColumn(name: 'sale_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Sale $sale = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->movementType = 'stock_in';
    }

    public function getId(): ?int
    {
        return $this->id ? (int)$this->id : null;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getMovementType(): string
    {
        return $this->movementType;
    }

    public function setMovementType(string $movementType): static
    {
        $this->movementType = strtolower($movementType);
        return $this;
    }

    public function getType(): string
    {
        return match ($this->movementType) {
            'stock_in' => 'IN',
            'stock_out', 'sale' => 'OUT',
            default => 'ADJUST',
        };
    }

    public function setType(string $type): static
    {
        $upper = strtoupper(trim($type));
        $this->movementType = match ($upper) {
            'IN' => 'stock_in',
            'OUT' => 'sale',
            default => 'adjustment',
        };
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = max(0, $quantity);
        return $this;
    }

    // Compatibility methods for previous / new quantities
    public function getPreviousQuantity(): int
    {
        return $this->product ? max(0, $this->product->getQuantity() - $this->quantity) : 0;
    }

    public function setPreviousQuantity(int $qty): static
    {
        return $this;
    }

    public function getNewQuantity(): int
    {
        return $this->product ? $this->product->getQuantity() : 0;
    }

    public function setNewQuantity(int $qty): static
    {
        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): static
    {
        $this->remarks = $remarks;
        return $this;
    }

    public function getReference(): ?string
    {
        return $this->remarks;
    }

    public function setReference(?string $reference): static
    {
        return $this->setRemarks($reference);
    }

    public function getSale(): ?Sale
    {
        return $this->sale;
    }

    public function setSale(?Sale $sale): static
    {
        $this->sale = $sale;
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
}
