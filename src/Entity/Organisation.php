<?php

namespace App\Entity;

use App\Enum\StatusEnum;
use App\Enum\TownEnum;
use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
#[ORM\Table(name: '`organisation`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(
    fields: ['email'],
    message: 'Cet email est déjà utilisé.'
)]
class Organisation implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $address = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(enumType: TownEnum::class)]
    private ?TownEnum $town = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[Assert\Email(message: "L'Email que vous avez renseigné n'est pas valide")]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $email = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(length: 30, nullable: false)]
    #[Assert\Regex(pattern: '/^\+?[0-9\s\-().]{6,20}$/', message: 'Le numéro de téléphone n\'est pas valide.')]
    private ?string $phone = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(enumType: StatusEnum::class)]
    private ?StatusEnum $status = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $firstName = null;

    #[Assert\NotBlank(message: "Ce champ doit être renseigné")]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $lastName = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(length: 20)]
    private array $roles = [];

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'organisation', cascade: ['remove'])]
    private Collection $events;


    
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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getTown(): ?TownEnum
    {
        return $this->town;
    }

    public function setTown(TownEnum $town): static
    {
        $this->town = $town;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getStatus(): ?StatusEnum
    {
        return $this->status;
    }

    public function setStatus(StatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_ORGANISATION
        if (!in_array('ROLE_ADMIN', $roles, true) && !in_array('ROLE_ORGANISATION', $roles, true)) {
            $roles[] = 'ROLE_ORGANISATION';
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

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
            $event->setOrganisation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getOrganisation() === $this) {
                $event->setOrganisation(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

        /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    // public function __toString(): string
    // {
    //     return $this->name ?? '';
    // }

}
