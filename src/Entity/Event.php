<?php

namespace App\Entity;

use App\Enum\FeeEnum;
use App\Enum\PublicEnum;
use App\Enum\ThematicEnum;
use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Organisation $organisation;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $endDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poster = null;

    #[ORM\Column(enumType: FeeEnum::class)]
    private ?FeeEnum $fee = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(enumType: ThematicEnum::class)]
    private ?ThematicEnum $thematic = null;

     #[ORM\Column(enumType: PublicEnum::class)]
    private ?PublicEnum $public = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private Location $location;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
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

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(?string $poster): static
    {
        $this->poster = $poster;

        return $this;
    }

    public function getFee(): ?FeeEnum
    {
        return $this->fee;
    }

    public function setFee(FeeEnum $fee): self
    {
        $this->fee = $fee;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getThematic(): ?ThematicEnum
    {
        return $this->thematic;
    }

    public function setThematic(ThematicEnum $thematic): self
    {
        $this->thematic = $thematic;

        return $this;
    }

    public function getPublic(): ?PublicEnum
    {
        return $this->public;
    }

    public function setPublic(PublicEnum $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }
}
