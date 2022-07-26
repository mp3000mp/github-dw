<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'dw_repository_package')]
class RepositoryPackage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[Groups(['all'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 55)]
    #[Groups(['all'])]
    private string $versionStr;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['all'])]
    private int $versionMinMajor;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['all'])]
    private int $versionMinMinor;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['all'])]
    private int $versionMinPatch;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['all'])]
    private int $versionMaxMajor;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['all'])]
    private int $versionMaxMinor;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['all'])]
    private int $versionMaxPatch;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['all'])]
    private bool $valid;

    #[ORM\ManyToOne(targetEntity: RepositoryPackageTypeFile::class)]
    #[ORM\JoinColumn(nullable: false)]
    private RepositoryPackageTypeFile $repositoryPackageTypeFile;

    #[ORM\ManyToOne(targetEntity: Package::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['all'])]
    private Package $package;

    #[ORM\ManyToOne(targetEntity: Repository::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Repository $repository;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersionStr(): string
    {
        return $this->versionStr;
    }

    public function setVersionStr(string $versionStr): void
    {
        $this->versionStr = $versionStr;
    }

    public function getVersionMinMajor(): int
    {
        return $this->versionMinMajor;
    }

    public function setVersionMinMajor(int $versionMinMajor): void
    {
        $this->versionMinMajor = $versionMinMajor;
    }

    public function getVersionMinMinor(): int
    {
        return $this->versionMinMinor;
    }

    public function setVersionMinMinor(int $versionMinMinor): void
    {
        $this->versionMinMinor = $versionMinMinor;
    }

    public function getVersionMinPatch(): int
    {
        return $this->versionMinPatch;
    }

    public function setVersionMinPatch(int $versionMinPatch): void
    {
        $this->versionMinPatch = $versionMinPatch;
    }

    public function getVersionMaxMajor(): int
    {
        return $this->versionMaxMajor;
    }

    public function setVersionMaxMajor(int $versionMaxMajor): void
    {
        $this->versionMaxMajor = $versionMaxMajor;
    }

    public function getVersionMaxMinor(): int
    {
        return $this->versionMaxMinor;
    }

    public function setVersionMaxMinor(int $versionMaxMinor): void
    {
        $this->versionMaxMinor = $versionMaxMinor;
    }

    public function getVersionMaxPatch(): int
    {
        return $this->versionMaxPatch;
    }

    public function setVersionMaxPatch(int $versionMaxPatch): void
    {
        $this->versionMaxPatch = $versionMaxPatch;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getRepositoryPackageTypeFile(): RepositoryPackageTypeFile
    {
        return $this->repositoryPackageTypeFile;
    }

    public function setRepositoryPackageTypeFile(RepositoryPackageTypeFile $repositoryPackageTypeFile): void
    {
        $this->repositoryPackageTypeFile = $repositoryPackageTypeFile;
    }

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function setPackage(Package $package): void
    {
        $this->package = $package;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function setRepository(Repository $repository): void
    {
        $this->repository = $repository;
    }
}
