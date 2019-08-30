<?php

namespace Somar\SilverStripe\APIClient\Validator;

use Exception;
use RuntimeException;
use InvalidArgumentException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OpenAPIValidation\PSR7\ValidatorBuilder;

class OpenAPI
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $type;

    public function __construct($path, $type = 'json')
    {
        if (!in_array(strtolower($type), ['json', 'yaml', 'url'])) {
            throw new InvalidArgumentException('The type must be one of the following: json, yaml or url.');
        }

        switch($type) {
            case 'url':
                if (!filter_var($path, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                    throw new InvalidArgumentException('The path must be a valid url and contain a path.');
                }

                $this->path = $path;
                break;

            default:
                if (!file_exists($path)) {
                    throw new RuntimeException('File: ' . $path . ' does not exist.');
                }

                $this->path = $path;
        }

        $this->type = strtolower($type);
    }

    public function getSchema()
    {
        if ($this->type == 'url') {
            $schema = $this->loadFromUrl();
        } else {
            $schema = $this->loadFromFile();
        }

        return $schema;
    }

    public function validateRequest(Request $request)
    {
        $validator = $this->getValidator()->getRequestValidator();

        return $validator->validate($request);
    }

    public function validateResponse(Response $response)
    {
        $validator = $this->getValidator()->getResponseValidator();

        return $validator->validate($response);
    }

    public function getValidator()
    {
        switch ($this->type) {
            case 'yaml':
                return (new ValidatorBuilder())->fromYaml($this->getSchema());
                break;

            case 'json':
                return (new ValidatorBuilder())->fromJson($this->getSchema());
                break;
        }
    }

    private function loadFromUrl()
    {
        $client = new Client();

        try {
            $response = $client->get($this->path);

            switch ($response->getHeader('Content-Type')) {
                case 'text/vnd.yaml':
                case 'text/yaml':
                case 'text/x-yaml':
                case 'application/x-yaml':
                case 'application/yaml':
                    $this->type = 'yaml';
                    break;

                case 'application/json':
                    $this->type = 'json';
                    break;
            }

            return (string) $repsonse->getBody();
        } catch (Exception $e) {
            throw new RuntimeException('Unable to retrieve data from: ' . $this->path);
        }
    }

    private function loadFromFile()
    {
        $spec = file_get_contents($this->path);

        if ($spec === false) {
            throw new RuntimeException('Unable to retrieve data from: ' . $this->path);
        }

        return $spec;
    }
}
