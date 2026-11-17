<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
#[ORM\Table(name: 'deliveries')]
class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(name: 'tracking_number', length: 50, unique: true)]
    private ?string $trackingNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $supplier = null;

    #[ORM\Column(name: 'expected_date', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $expectedDate = null;

    #[ORM\Column(name: 'received_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $receivedDate = null;

    #[ORM\Column(length: 50)]
    private string $status = 'Pending';

    #[ORM\Column(name: 'is_received', type: Types::BOOLEAN)]
    private bool $isReceived = false;

    #[ORM\Column(name: 'total_cost', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $totalCost = '0.00';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, DeliveryItem>
     */
    #[ORM\OneToMany(mappedBy: 'delivery', targetEntity: DeliveryItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->expectedDate = new \DateTime('+2 days');
        $this->trackingNumber = 'DEL-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    }

    public function getId(): ?int
    {
        return $this->id ? (int)$this->id : null;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    public function setSupplier(string $supplier): static
    {
        $this->supplier = $supplier;
        return $this;
    }

    public function getExpectedDate(): ?\DateTimeInterface
    {
        return $this->expectedDate;
    }

    public function setExpectedDate(\DateTimeInterface $expectedDate): static
    {
        $this->expectedDate = $expectedDate;
        return $this;
    }

    public function getReceivedDate(): ?\DateTimeInterface
    {
        return $this->receivedDate;
    }

    public function setReceivedDate(?\DateTimeInterface $receivedDate): static
    {
        $this->receivedDate = $receivedDate;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isReceived(): bool
    {
        return $this->isReceived;
    }

    public function setIsReceived(bool $isReceived): static
    {
        $this->isReceived = $isReceived;
        return $this;
    }

    public function getTotalCost(): string
    {
        return $this->totalCost;
    }

    public function setTotalCost(string|float $totalCost): static
    {
        $this->totalCost = number_format((float)$totalCost, 2, '.', '');
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, DeliveryItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(DeliveryItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setDelivery($this);
        }
        return $this;
    }

    public function removeItem(DeliveryItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getDelivery() === $this) {
                $item->setDelivery(null);
            }
        }
        return $this;
    }

    public function getItemsSummary(): string
    {
        if ($this->items->isEmpty()) {
            return $this->notes ?: 'No items listed';
        }

        $parts = [];
        foreach ($this->items as $item) {
            $parts[] = $item->getProductName() . ' (' . $item->getQuantity() . ')';
        }
        return implode(', ', $parts);
    }
}
