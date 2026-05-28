<?php

namespace App\Entity;

use App\Enum\TownEnum;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name : 'location')]
#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(enumType: TownEnum::class)]
    private ?TownEnum $town = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 8,  nullable: true)]
    #[Assert\Regex(pattern: '/^[-+]?([0-8]?\d(\.\d+)?|90(\.0+)?)$/', message: 'Latitude invalide')]
    private ?string $lat = null;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    #[Assert\Regex(pattern: '/^[-+]?((1[0-7]\d|0?\d?\d)(\.\d+)?|180(\.0+)?)$/', message: 'Longitude invalide')]
    private ?string $lon = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $information = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'location')]
    private Collection $events;

    /**
     * @var Organisation<int, organisation>
     */
    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private Organisation $organisation;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getTown(): ?TownEnum
    {
        return $this->town;
    }

    public function setTown(?TownEnum $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): static
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?string
    {
        return $this->lon;
    }

    public function setLon(?string $lon): static
    {
        $this->lon = $lon;

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): static
    {
        $this->information = $information;

        return $this;
    }

     public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): static
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setLocation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            // if ($event->getLocation() === $this) {
            //     $event->setLocation(null);
            // }
        }

        return $this;
    }
}
