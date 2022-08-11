<?php

declare(strict_types=1);

namespace App\Helper\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class JsonResponseHelper
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed    $entity
     * @param string[] $serializationGroups
     */
    public function createResponse($entity, array $serializationGroups, int $status = 200): Response
    {
        return new Response(
            $this->serializer->serialize($entity, 'json', ['groups' => $serializationGroups]),
            $status,
            ['content-type' => 'application/json'],
        );
    }
}
