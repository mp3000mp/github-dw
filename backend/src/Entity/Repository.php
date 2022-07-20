<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RepositoryRepository")
 * @ORM\Table(name="dw_repository")
 * @UniqueEntity(fields="url", message="This url is not available")
 */
class Repository
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint", options={"unsigned":true})
     * @Groups({"all"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Groups({"all"})
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Groups({"all"})
     */
    private string $username;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"admin"})
     */
    private string $mainLanguage;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"all"})
     */
    private string $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $fullName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"admin"})
     */
    private string $routineError;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Groups({"all"})
     */
    private string $description;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"all"})
     */
    private string $licenseName;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned":true})
     * @Groups({"all"})
     */
    private int $forksCount;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned":true})
     * @Groups({"all"})
     */
    private int $openIssuesCount;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned":true})
     * @Groups({"all"})
     */
    private int $stargazersCount;

    /**
     * @ORM\Column(type="bigint", nullable=true, options={"unsigned":true})
     * @Groups({"admin"})
     */
    private int $githubId;

    /**
     * @ORM\Column(type="bigint", nullable=true, options={"unsigned":true})
     */
    private int $size;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Groups({"admin"})
     */
    private DateTime $routine1At;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"admin"})
     */
    private DateTime $routine2At;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"all"})
     */
    private DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"all"})
     */
    private DateTime $pushedAt;

    /**
     * @var Collection<int, RepositoryLanguage>
     *
     * @ORM\OneToMany(targetEntity="RepositoryLanguage", mappedBy="repository")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private Collection $Languages;

    /**
     * @var Collection<int, RepositoryTopic>
     *
     * @ORM\OneToMany(targetEntity="RepositoryTopic", mappedBy="repository")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private Collection $topics;

    /**
     * @var Collection<int, RepositoryPackageTypeFile>
     *
     * @ORM\OneToMany(targetEntity="RepositoryPackageTypeFile", mappedBy="repository")
     */
    private Collection $packageTypeFiles;

}
