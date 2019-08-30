<?php

namespace Somar\SilverStripe\APIClient\Test;

use InvalidArgumentException;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Somar\SilverStripe\APIClient\Validator\OpenAPI;

class OpenAPIValidatorTest extends TestCase
{
    public function testValidInstance()
    {
        $validator = new OpenAPI(__DIR__ . '/schemas/petstore.yml', 'yaml');
        $this->assertEquals(get_class($validator), 'Somar\SilverStripe\APIClient\Validator\OpenAPI');

        $validator = new OpenAPI(__DIR__ . '/schemas/petstore.json', 'json');
        $this->assertEquals(get_class($validator), 'Somar\SilverStripe\APIClient\Validator\OpenAPI');
    }

    public function testInvalidInstance()
    {
        $this->expectException(InvalidArgumentException::class);
        $validator = new OpenAPI(__DIR__ . '/schemas/petstore.yml', 'text');

        $this->expectException(RuntimeException::class);
        $validator = new OpenAPI(__DIR__ . '/schemas/petstore.txt', 'yaml');
    }

    public function testLoadValidYaml()
    {
        $validator = new OpenAPI(__DIR__ . '/schemas/petstore.yml', 'yaml');
        $schema = $validator->getSchema();

        $this->assertEquals($schema, file_get_contents(__DIR__ . '/schemas/petstore.yml'));
    }

    public function testLoadValidJson()
    {
        $validator = new OpenAPI(__DIR__ . '/schemas/petstore.json', 'json');
        $schema = $validator->getSchema();

        $this->assertEquals($schema, file_get_contents(__DIR__ . '/schemas/petstore.json'));
    }

    public function testValidRequest()
    {

    }

    public function testInvalidRequest()
    {

    }

    public function testValidResponse()
    {

    }

    public function testInvalidResponse()
    {

    }
}
