<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User.
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="This email is not available")
 * @UniqueEntity(fields="username", message="This username is not available")
 */
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"admin"})
     */
    private ?int $id = null;

    /**
     * @Assert\Email()
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=100, unique=true)
     * @Groups({"admin"})
     */
    private string $email;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=55, unique=true)
     * @Groups({"admin", "me"})
     */
    private string $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $password;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $password_updated_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $reset_password_token;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     */
    private ?DateTime $reset_password_at;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"admin"})
     */
    private bool $isEnabled = false;

    /**
     * @ORM\Column(type="json", nullable=false)
     * @Groups({"me", "admin"})
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isSuperAdmin = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns the roles or permissions granted to the users for security.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return $roles;
    }

    public function setRoles(array $roles): self
    {
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->username,
            $this->email,
            $this->password,
            $this->isEnabled,
            // see section on salt below
            // $this->salt,
        ];
    }

    public function __unserialize(array $serialized): void
    {
        [$this->id, $this->username, $this->email, $this->password, $this->isEnabled,
            // $this->salt
        ] = $serialized;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPasswordUpdatedAt(): ?DateTime
    {
        return $this->password_updated_at;
    }

    public function setPasswordUpdatedAt(?DateTime $password_updated_at): void
    {
        $this->password_updated_at = $password_updated_at;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->reset_password_token;
    }

    public function setResetPasswordToken(?string $reset_password_token): void
    {
        $this->reset_password_token = $reset_password_token;
    }

    public function generateResetPasswordToken(): void
    {
        $this->reset_password_token = md5(random_bytes(64));
    }

    public function getResetPasswordAt(): ?DateTime
    {
        return $this->reset_password_at;
    }

    public function setResetPasswordAt(?DateTime $reset_password_at): void
    {
        $this->reset_password_at = $reset_password_at;
    }

    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function getIsSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }

    public function setIsSuperAdmin(bool $isSuperAdmin): void
    {
        $this->isSuperAdmin = $isSuperAdmin;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
