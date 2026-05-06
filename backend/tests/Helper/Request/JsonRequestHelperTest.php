<?php

namespace App\Tests\Helper\Request;

use App\Helper\Request\Exception\JsonSchemaException;
use App\Helper\Request\JsonRequestHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JsonRequestHelperTest extends KernelTestCase
{
    private JsonRequestHelper $helper;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->helper = self::getContainer()->get(JsonRequestHelper::class);
    }

    public function testHandleRequestThrowsOnInvalidJson(): void
    {
        $this->expectException(JsonSchemaException::class);
        $this->expectExceptionMessage('Invalid request content');

        $this->helper->handleRequest('{}', 'package_autocomplete');
    }

    public function testHandleRequestDeserializesIntoClass(): void
    {
        $rawData = '{"language":"PHP","text":"foo"}';
        $result = $this->helper->handleRequest($rawData, 'package_autocomplete', PackageAutocompleteDto::class);

        self::assertInstanceOf(PackageAutocompleteDto::class, $result);
        self::assertSame('PHP', $result->language);
        self::assertSame('foo', $result->text);
    }
}

class PackageAutocompleteDto
{
    public string $language;
    public string $text;
}
