<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="dw_repository_package_type_file")
 */
class RepositoryPackageTypeFile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint", options={"unsigned":true})
     * @Groups({"admin"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"admin"})
     */
    private string $path;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"admin"})
     */
    private string $routineError;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private string $sha;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Groups({"admin"})
     */
    private DateTime $routine1At;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"admin"})
     */
    private DateTime $routine3At;

    /**
     * @ORM\ManyToOne(targetEntity="Repository")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"admin"})
     */
    private Repository $repository;

    /**
     * @ORM\ManyToOne(targetEntity="PackageTypeFile")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"admin"})
     */
    private PackageTypeFile $packageTypeFile;

    /**
     * @var Collection<int, RepositoryPackage>
     *
     * @ORM\OneToMany(targetEntity="RepositoryPackage", mappedBy="repositoryPackageTypeFile")
     */
    private Collection $repositoryPackage;

}
