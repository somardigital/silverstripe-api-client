<?php

namespace Somar\SilverStripe\APIClient;

use Exception;
use InvalidArgumentException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client as HttpClient;
use Somar\SilverStripe\APIClient\Validator\OpenAPI;

class Client extends HttpClient
{
    /**
     * @var Somar\SilverStripe\APIClient\Validator\OpenAPI
     */
    protected $validator;

    /**
     * @param string $openApiSchema Path or URL to the Open API schema
     * @param array  $config        GuzzleHttp\Client config
     */
    public function __construct(string $openApiSchema, array $config = [])
    {
        parent::__construct($config);

        $type = null;

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $type = 'url';
        } else {
            $ext = strtolower(pathinfo($openApiSchema, PATHINFO_EXTENSION));

            if (in_array($ext, ['yml', 'yaml'])) {
                $type = 'yaml';
            }

            if ($ext == 'json') {
                $type = 'json';
            }
        }

        if (!$type) {
            throw new InvalidArgumentException('Unsupported Open Api schema type.');
        }

        $this->validator = new Validator($this->openApiSchema, $type);
    }

    /**
     * @param string $uri       Request uri
     * @param array  $options   Request options
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function get(string $uri = '', array $options = [])
    {
        $request = $this->createRequest('GET', $uri, $options);
        $this->validator->validateRequest($request);

        $response = $this->send($request, $options);
        $this->validator->validateResponse($response);

        return $response;
    }

    /**
     * @param string $uri       Request uri
     * @param array  $options   Request options
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function post(string $uri = '', array $options = [])
    {
        $request = $this->createRequest('POST', $uri, $options);
        $this->validator->validateRequest($request);

        $response = $this->send($request, $options);
        $this->validator->validateResponse($response);

        return $response;
    }

    /**
     * @param string $uri       Request uri
     * @param array  $options   Request options
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function put(string $uri = '', array $options = [])
    {
        $request = $this->createRequest('PUT', $uri, $options);
        $this->validator->validateRequest($request);

        $response = $this->send($request, $options);
        $this->validator->validateResponse($response);

        return $response;
    }

    /**
     * @param string $uri       Request uri
     * @param array  $options   Request options
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function delete(string $uri = '', array $options = [])
    {
        $request = $this->createRequest('DELETE', $uri, $options);
        $this->validator->validateRequest($request);

        $response = $this->send($request, $options);
        $this->validator->validateResponse($response);

        return $response;
    }

    /**
     * @param string $method    HTTP Method for request
     * @param string $uri       Request uri
     * @param array  $options   Request options
     *
     * @return GuzzleHttp\Psr7\Request
     */
    private function createRequest(string $method, string $uri = '', array $options = [])
    {
        $options = $this->prepareDefaults($options);

        // Remove request modifying parameter because it can be done up-front.
        $headers = isset($options['headers']) ? $options['headers'] : [];
        $body = isset($options['body']) ? $options['body'] : null;
        $version = isset($options['version']) ? $options['version'] : '1.1';

        // Merge the URI into the base URI.
        $uri = $this->buildUri($uri, $options);

        if (is_array($body)) {
            $this->invalidBody();
        }

        return new Request($method, $uri, $headers, $body, $version);
    }
}
