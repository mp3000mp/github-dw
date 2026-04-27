<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RepositoryPackageTypeFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RepositoryPackageTypeFileRepository::class)]
#[ORM\Table(name: 'dw_repository_package_type_file')]
#[ORM\UniqueConstraint(name: 'idx_uniq', columns: ['repository_id', 'package_type_file_id', 'path'])]
class RepositoryPackageTypeFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[Groups(['admin'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['admin'])]
    private string $path;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['admin'])]
    private ?string $routineError;

    #[ORM\Column(type: 'string', length: 100)]
    private string $sha;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['admin'])]
    private \DateTime $routine1At;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['admin'])]
    private ?\DateTime $routine3At;

    #[ORM\ManyToOne(targetEntity: Repository::class, inversedBy: 'repositoryPackageTypeFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private Repository $repository;

    #[ORM\ManyToOne(targetEntity: PackageTypeFile::class, inversedBy: 'repositoryPackageTypeFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private PackageTypeFile $packageTypeFile;

    /**
     * @var Collection<int, RepositoryPackage>
     */
    #[ORM\OneToMany(mappedBy: 'repositoryPackageTypeFile', targetEntity: RepositoryPackage::class)]
    #[Groups(['all'])]
    private Collection $repositoryPackages;

    public function __construct()
    {
        $this->repositoryPackages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getRoutineError(): ?string
    {
        return $this->routineError;
    }

    public function setRoutineError(?string $routineError): void
    {
        $this->routineError = $routineError;
    }

    public function getSha(): string
    {
        return $this->sha;
    }

    public function setSha(string $sha): void
    {
        $this->sha = $sha;
    }

    public function getRoutine1At(): \DateTime
    {
        return $this->routine1At;
    }

    public function setRoutine1At(\DateTime $routine1At): void
    {
        $this->routine1At = $routine1At;
    }

    public function getRoutine3At(): ?\DateTime
    {
        return $this->routine3At;
    }

    public function setRoutine3At(?\DateTime $routine3At): void
    {
        $this->routine3At = $routine3At;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function setRepository(Repository $repository): void
    {
        $this->repository = $repository;
    }

    public function getPackageTypeFile(): PackageTypeFile
    {
        return $this->packageTypeFile;
    }

    public function setPackageTypeFile(PackageTypeFile $packageTypeFile): void
    {
        $this->packageTypeFile = $packageTypeFile;
    }

    /**
     * @return Collection<int, RepositoryPackage>
     */
    public function getRepositoryPackages(): Collection
    {
        return $this->repositoryPackages;
    }

    public function addRepositoryPackage(RepositoryPackage $repositoryPackage): void
    {
        if (!$this->repositoryPackages->contains($repositoryPackage)) {
            $this->repositoryPackages->add($repositoryPackage);
        }
    }

    public function removeRepositoryPackage(RepositoryPackage $repositoryPackage): void
    {
        if ($this->repositoryPackages->contains($repositoryPackage)) {
            $this->repositoryPackages->removeElement($repositoryPackage);
        }
    }
}
