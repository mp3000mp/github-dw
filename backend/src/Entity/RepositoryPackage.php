<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RepositoryPackageRepository")
 * @ORM\Table(name="dw_repository_package")
 */
class RepositoryPackage
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
     * @ORM\Column(type="string", length=55, nullable=false)
     * @Groups({"all"})
     */
    private string $versionStr;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     * @Groups({"all"})
     */
    private int $versionMinMajor;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     * @Groups({"all"})
     */
    private int $versionMinMinor;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     * @Groups({"all"})
     */
    private int $versionMinPatch;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     * @Groups({"all"})
     */
    private int $versionMaxMajor;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     * @Groups({"all"})
     */
    private int $versionMaxMinor;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     * @Groups({"all"})
     */
    private int $versionMaxPatch;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     * @Groups({"all"})
     */
    private bool $valid;

    /**
     * @ORM\ManyToOne(targetEntity="RepositoryPackageTypeFile")
     * @ORM\JoinColumn(nullable=false)
     */
    private RepositoryPackageTypeFile $repositoryPackageTypeFile;

}
