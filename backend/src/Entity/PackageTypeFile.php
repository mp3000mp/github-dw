<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PackageTypeFileRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PackageTypeFileRepository::class)]
#[ORM\Table(name: 'dw_package_type_file')]
#[UniqueEntity(fields: 'file', message: 'This file is not available')]
class PackageTypeFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[Groups(['admin'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    #[Groups(['admin'])]
    private string $file;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    #[Groups(['admin'])]
    private string $language;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    #[Groups(['admin'])]
    private string $name;

    #[ORM\Column(type: 'integer', options: ['default' => 100, 'unsigned' => true])]
    #[Groups(['admin'])]
    private int $githubCurrentSize;

    #[ORM\Column(type: 'integer', options: ['default' => 1, 'unsigned' => true])]
    #[Groups(['admin'])]
    private int $githubCurrentPage;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Groups(['admin'])]
    private DateTime $updatedAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['admin'])]
    private bool $priority;

    /**
     * @var Collection<int, RepositoryPackageTypeFile>
     */
    #[ORM\OneToMany(mappedBy: 'packageTypeFile', targetEntity: RepositoryPackageTypeFile::class)]
    private Collection $repositoryPackageTypeFiles;

    public function __construct()
    {
        $this->repositoryPackageTypeFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getGithubCurrentSize(): int
    {
        return $this->githubCurrentSize;
    }

    public function setGithubCurrentSize(int $githubCurrentSize): void
    {
        $this->githubCurrentSize = $githubCurrentSize;
    }

    public function getGithubCurrentPage(): int
    {
        return $this->githubCurrentPage;
    }

    public function setGithubCurrentPage(int $githubCurrentPage): void
    {
        $this->githubCurrentPage = $githubCurrentPage;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function isPriority(): bool
    {
        return $this->priority;
    }

    public function setPriority(bool $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return Collection<int, RepositoryPackageTypeFile>
     */
    public function getRepositoryPackageTypeFiles(): Collection
    {
        return $this->repositoryPackageTypeFiles;
    }

    public function addPackageTypeFile(RepositoryPackageTypeFile $packageTypeFile): void
    {
        if (!$this->repositoryPackageTypeFiles->contains($packageTypeFile)) {
            $this->repositoryPackageTypeFiles->add($packageTypeFile);
        }
    }

    public function removePackageTypeFile(RepositoryPackageTypeFile $packageTypeFile): void
    {
        if ($this->repositoryPackageTypeFiles->contains($packageTypeFile)) {
            $this->repositoryPackageTypeFiles->removeElement($packageTypeFile);
        }
    }
}
