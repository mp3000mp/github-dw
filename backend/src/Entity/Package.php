<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
#[ORM\Table(name: 'dw_package')]
#[ORM\UniqueConstraint(name: 'idx_uniq', columns: ['package_type_file_id', 'name'])]
class Package
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[Groups(['all', 'autocomplete'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['all', 'autocomplete'])]
    private string $name;

    #[ORM\ManyToOne(targetEntity: PackageTypeFile::class)]
    #[ORM\JoinColumn(nullable: false)]
    private PackageTypeFile $packageTypeFile;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $nb = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPackageTypeFile(): PackageTypeFile
    {
        return $this->packageTypeFile;
    }

    public function setPackageTypeFile(PackageTypeFile $packageTypeFile): void
    {
        $this->packageTypeFile = $packageTypeFile;
    }

    public function getNb(): int
    {
        return $this->nb;
    }

    public function setNb(int $nb): void
    {
        $this->nb = $nb;
    }
}
