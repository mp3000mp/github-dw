<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PackageTypeFileRepository")
 * @UniqueEntity(fields="file", message="This file is not available")
 */
class PackageTypeFile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint", options={"unsigned":true})
     * @Groups({"admin"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     * @Groups({"admin"})
     */
    private string $file;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     * @Groups({"admin"})
     */
    private string $language;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     * @Groups({"admin"})
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":100,"unsigned":true})
     * @Groups({"admin"})
     */
    private int $githubCurrentSize;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":1,"unsigned":true})
     * @Groups({"admin"})
     */
    private int $githubCurrentPage;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Groups({"admin"})
     */
    private DateTime $updatedAt;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * @Groups({"admin"})
     */
    private bool $priority;

    /**
     * @var Collection<int, RepositoryPackageTypeFile>
     *
     * @ORM\OneToMany(targetEntity="RepositoryPackageTypeFile", mappedBy="packageTypeFile")
     */
    private Collection $packageTypeFiles;

}
