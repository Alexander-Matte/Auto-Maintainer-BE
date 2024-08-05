<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarRepository::class)]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $make = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model = null;

    #[ORM\ManyToOne(inversedBy: 'car')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    /**
     * @var Collection<int, MaintenanceActivity>
     */
    #[ORM\OneToMany(targetEntity: MaintenanceActivity::class, mappedBy: 'car')]
    private Collection $maintenanceActivities;

    public function __construct()
    {
        $this->maintenanceActivities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMake(): ?string
    {
        return $this->make;
    }

    public function setMake(?string $make): static
    {
        $this->make = $make;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return Collection<int, MaintenanceActivity>
     */
    public function getMaintenanceActivities(): Collection
    {
        return $this->maintenanceActivities;
    }

    public function addMaintenanceActivity(MaintenanceActivity $maintenanceActivity): static
    {
        if (!$this->maintenanceActivities->contains($maintenanceActivity)) {
            $this->maintenanceActivities->add($maintenanceActivity);
            $maintenanceActivity->setCar($this);
        }

        return $this;
    }

    public function removeMaintenanceActivity(MaintenanceActivity $maintenanceActivity): static
    {
        if ($this->maintenanceActivities->removeElement($maintenanceActivity)) {
            // set the owning side to null (unless already changed)
            if ($maintenanceActivity->getCar() === $this) {
                $maintenanceActivity->setCar(null);
            }
        }

        return $this;
    }
}
