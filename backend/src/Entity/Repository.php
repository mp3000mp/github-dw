<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RepositoryRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RepositoryRepository::class)]
#[ORM\Table(name: 'dw_repository')]
#[UniqueEntity(fields: 'url', message: 'This url is not available')]
class Repository
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[Groups(['all'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['all'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['all'])]
    private string $username;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $mainLanguage;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['all'])]
    private string $url;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['all'])]
    private ?string $fullName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['admin'])]
    private ?string $routineError;

    #[ORM\Column(type: 'string', length: 4000, nullable: true)]
    #[Groups(['all'])]
    private ?string $description;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['all'])]
    private ?string $licenseName;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    #[Groups(['all'])]
    private ?int $forksCount;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    #[Groups(['all'])]
    private ?int $openIssuesCount;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    #[Groups(['all'])]
    private ?int $stargazersCount;

    #[ORM\Column(type: 'bigint', nullable: true, options: ['unsigned' => true])]
    #[Groups(['admin'])]
    private ?int $githubId;

    #[ORM\Column(type: 'bigint', nullable: true, options: ['unsigned' => true])]
    #[Groups(['admin'])]
    private ?int $size;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['admin'])]
    private DateTime $routine1At;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['admin'])]
    private ?DateTime $routine2At;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['all'])]
    private ?DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['all'])]
    private ?DateTime $pushedAt;

    /**
     * @var Collection<int, RepositoryLanguage>
     */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: RepositoryLanguage::class)]
    #[ORM\JoinColumn]
    #[Groups(['admin'])]
    private Collection $languages;

    /**
     * @var Collection<int, RepositoryTopic>
     */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: RepositoryTopic::class)]
    #[ORM\JoinColumn]
    #[Groups(['all'])]
    private Collection $topics;

    /**
     * @var Collection<int, RepositoryPackageTypeFile>
     */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: RepositoryPackageTypeFile::class)]
    #[ORM\JoinColumn]
    #[Groups(['admin'])]
    private Collection $repositoryPackageTypeFiles;

    /**
     * @var Collection<int, RepositoryPackage>
     */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: RepositoryPackage::class)]
    #[ORM\JoinColumn]
    #[Groups(['admin'])]
    private Collection $repositoryPackages;

    public function __construct()
    {
        $this->languages = new ArrayCollection();
        $this->topics = new ArrayCollection();
        $this->repositoryPackageTypeFiles = new ArrayCollection();
    }

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

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getMainLanguage(): ?string
    {
        return $this->mainLanguage;
    }

    public function setMainLanguage(?string $mainLanguage): void
    {
        $this->mainLanguage = $mainLanguage;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getRoutineError(): ?string
    {
        return $this->routineError;
    }

    public function setRoutineError(?string $routineError): void
    {
        $this->routineError = $routineError;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getLicenseName(): ?string
    {
        return $this->licenseName;
    }

    public function setLicenseName(?string $licenseName): void
    {
        $this->licenseName = $licenseName;
    }

    public function getForksCount(): ?int
    {
        return $this->forksCount;
    }

    public function setForksCount(?int $forksCount): void
    {
        $this->forksCount = $forksCount;
    }

    public function getOpenIssuesCount(): ?int
    {
        return $this->openIssuesCount;
    }

    public function setOpenIssuesCount(?int $openIssuesCount): void
    {
        $this->openIssuesCount = $openIssuesCount;
    }

    public function getStargazersCount(): ?int
    {
        return $this->stargazersCount;
    }

    public function setStargazersCount(?int $stargazersCount): void
    {
        $this->stargazersCount = $stargazersCount;
    }

    public function getGithubId(): ?int
    {
        return $this->githubId;
    }

    public function setGithubId(?int $githubId): void
    {
        $this->githubId = $githubId;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function getRoutine1At(): DateTime
    {
        return $this->routine1At;
    }

    public function setRoutine1At(DateTime $routine1At): void
    {
        $this->routine1At = $routine1At;
    }

    public function getRoutine2At(): ?DateTime
    {
        return $this->routine2At;
    }

    public function setRoutine2At(?DateTime $routine2At): void
    {
        $this->routine2At = $routine2At;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getPushedAt(): ?DateTime
    {
        return $this->pushedAt;
    }

    public function setPushedAt(?DateTime $pushedAt): void
    {
        $this->pushedAt = $pushedAt;
    }

    /**
     * @return Collection<int, RepositoryLanguage>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(RepositoryLanguage $language): void
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
        }
    }

    public function removeLanguage(RepositoryLanguage $language): void
    {
        if ($this->languages->contains($language)) {
            $this->languages->removeElement($language);
        }
    }

    /**
     * @return Collection<int, RepositoryTopic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(RepositoryTopic $topic): void
    {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic);
        }
    }

    public function removeTopic(RepositoryTopic $topic): void
    {
        if ($this->topics->contains($topic)) {
            $this->topics->removeElement($topic);
        }
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

    /**
     * @return Collection<int, RepositoryPackage>
     */
    public function getRepositoryPackages(): Collection
    {
        return $this->repositoryPackages;
    }
}
