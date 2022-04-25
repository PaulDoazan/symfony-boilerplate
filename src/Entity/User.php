<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    use TimestampableEntity;
    use BlameableEntity;

    public const ROLE_MAP = [
        'super-admin' => 'ROLE_SUPER_ADMIN',
        'admin'       => 'ROLE_ADMIN',
        'user' => 'ROLE_USER',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    private ?string $plainPassword = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $firstName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $lastName;

    #[ORM\Column(type: 'string', length: 255)]
    private $Username;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSingleRole(): ?string {
        $roles = $this->getRoles();
        foreach (self::ROLE_MAP as $label => $role) {
            if (in_array($role, $roles)) {
                return $role;
            }
        }
        return null;
    }

    public function getSingleRoleKey(?bool $translated = false) {
        $flipped = array_flip( self::ROLE_MAP);
        return array_key_exists($this->getSingleRole(), $flipped) ? $flipped[$this->getSingleRole()] : null;
    }

    public function setSingleRole(string $role): User {
        $flipped = array_flip(self::ROLE_MAP);
        if (array_key_exists($role, $flipped)) {
            $this->setRoles([$role]);
        } else {
            $this->setRoles([]);
        }
        return $this;
    }

    public function isSuperAdmin(): bool {
        return $this->getSingleRole() === 'ROLE_SUPER_ADMIN';
    }

    public function isAdmin(): bool {
        return $this->getSingleRole() === 'ROLE_ADMIN';
    }

    public function isTeam(): bool {
        //return $this->isAdmin() || $this->isSuperAdmin();
        return $this->isSuperAdmin();
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setUsername(string $Username): self
    {
        $this->Username = $Username;

        return $this;
    }
}
