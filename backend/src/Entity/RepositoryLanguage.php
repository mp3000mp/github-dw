<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="dw_repository_language")
 */
class RepositoryLanguage
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint", options={"unsigned":true})
     * @Groups({"admin"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Groups({"admin"})
     */
    private string $language;

    /**
     * @ORM\Column(type="bigint", nullable=false, options={"unsigned":true})
     * @Groups({"admin"})
     */
    private int $weight;

    /**
     * @ORM\ManyToOne(targetEntity="Repository")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"admin"})
     */
    private Repository $repository;

}
