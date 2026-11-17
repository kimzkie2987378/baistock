<?php

namespace App\Entity;

use App\Repository\SaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaleRepository::class)]
#[ORM\Table(name: 'sales')]
class Sale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(name: 'receipt_number', length: 50, unique: true)]
    private ?string $receiptNumber = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(name: 'total_amount', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $totalAmount = '0.00';

    #[ORM\Column(name: 'amount_received', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $amountReceived = '0.00';

    #[ORM\Column(name: 'change_amount', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $changeAmount = '0.00';

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, SaleItem>
     */
    #[ORM\OneToMany(mappedBy: 'sale', targetEntity: SaleItem::class, cascade: ['persist', 'remove'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->receiptNumber = 'REC-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    }

    public function getId(): ?int
    {
        return $this->id ? (int)$this->id : null;
    }

    public function getReceiptNumber(): ?string
    {
        return $this->receiptNumber;
    }

    public function setReceiptNumber(string $receiptNumber): static
    {
        $this->receiptNumber = $receiptNumber;
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

    public function getCashier(): ?User
    {
        return $this->user;
    }

    public function setCashier(?User $user): static
    {
        return $this->setUser($user);
    }

    public function getCashierName(): string
    {
        if ($this->user) {
            return $this->user->getFullName() ?: $this->user->getUsername();
        }
        return 'Admin';
    }

    public function setCashierName(string $name): static
    {
        // Compatibility setter
        return $this;
    }

    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string|float $totalAmount): static
    {
        $this->totalAmount = number_format((float)$totalAmount, 2, '.', '');
        return $this;
    }

    public function getSubtotal(): string
    {
        return $this->totalAmount;
    }

    public function setSubtotal(string|float $subtotal): static
    {
        return $this->setTotalAmount($subtotal);
    }

    public function getTax(): string
    {
        return '0.00';
    }

    public function setTax(string|float $tax): static
    {
        return $this;
    }

    public function getAmountReceived(): string
    {
        return $this->amountReceived;
    }

    public function setAmountReceived(string|float $amountReceived): static
    {
        $this->amountReceived = number_format((float)$amountReceived, 2, '.', '');
        return $this;
    }

    public function getAmountPaid(): string
    {
        return $this->amountReceived;
    }

    public function setAmountPaid(string|float $amountPaid): static
    {
        return $this->setAmountReceived($amountPaid);
    }

    public function getChangeAmount(): string
    {
        return $this->changeAmount;
    }

    public function setChangeAmount(string|float $changeAmount): static
    {
        $this->changeAmount = number_format((float)$changeAmount, 2, '.', '');
        return $this;
    }

    public function getPaymentMethod(): string
    {
        return 'Cash';
    }

    public function setPaymentMethod(string $method): static
    {
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

    /**
     * @return Collection<int, SaleItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(SaleItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setSale($this);
        }
        return $this;
    }

    public function removeItem(SaleItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getSale() === $this) {
                $item->setSale(null);
            }
        }
        return $this;
    }

    public function getPrimaryProductName(): string
    {
        if ($this->items->isEmpty()) {
            return 'General Sale';
        }
        $first = $this->items->first();
        $name = $first->getProductName();
        if ($this->items->count() > 1) {
            $name .= ' +' . ($this->items->count() - 1) . ' more';
        }
        return $name;
    }

    public function getTotalUnits(): int
    {
        $sum = 0;
        foreach ($this->items as $item) {
            $sum += $item->getQuantity();
        }
        return $sum;
    }
}
