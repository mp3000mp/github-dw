<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 */
class RepositoryTopic
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
    private string $topic;

    /**
     * @ORM\ManyToOne(targetEntity="Repository")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"all"})
     */
    private Repository $repository;

}
