<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="dw_repository_topic")
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
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
