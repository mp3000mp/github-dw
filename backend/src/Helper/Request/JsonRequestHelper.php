<?php

declare(strict_types=1);

namespace App\Helper\Request;

use App\Helper\Request\Exception\EntityValidationException;
use App\Helper\Request\Exception\JsonSchemaException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JsonRequestHelper
{
    private string $pathSchemas;
    private SerializerInterface $serialiser;
    private Validator $jsonValidator;
    private LoggerInterface $logger;
    private ValidatorInterface $validator;

    public function __construct(ParameterBagInterface $parameterBag, SerializerInterface $serializer, ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->pathSchemas = $parameterBag->get('PATH_SCHEMAS');
        $this->serialiser = $serializer;
        $this->jsonValidator = new Validator();
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @template T
     *
     * @param mixed            $rawData
     * @param ?class-string<T> $class
     * @param T|null           $entity
     *
     * @return T|\stdClass
     */
    public function handleRequest($rawData, string $schema, ?string $class = null, $entity = null)
    {
        // json schema
        $jsonData = json_decode($rawData);
        $jsonSchema = json_decode(file_get_contents($this->pathSchemas.$schema.'.json'));
        $result = $this->jsonValidator->validate($jsonData, $jsonSchema);
        if (!$result->isValid()) {
            $err = "JSON does not validate. Violations:\n";

            $formatter = new ErrorFormatter();
            $errors = $formatter->formatKeyed($result->error());
            foreach ($errors as $k => $error) {
                $err .= sprintf("[%s] %s\n", $k, implode(', ', $error));
            }
            $this->logger->error($err);
            // throw new JsonSchemaException(400, '$err');
            throw new JsonSchemaException(400, 'Invalid request content.');
        }

        if (null === $class) {
            return $jsonData;
        }

        // entity validation
        $context = [];
        if (null !== $entity) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $entity;
        }
        $objData = $this->serialiser->deserialize($rawData, $class, 'json', $context);
        $errors = $this->validator->validate($objData);
        if (count($errors)) {
            $err = "Entity does not validate. Violations:\n";
            foreach ($errors as $error) {
                $err .= sprintf("[%s=%s] %s\n", $error->getPropertyPath(), $error->getInvalidValue(), $error->getMessage());
            }
            throw new EntityValidationException(400, $err);
        }

        return $objData;
    }
}
