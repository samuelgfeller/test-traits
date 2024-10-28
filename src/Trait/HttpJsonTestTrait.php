<?php

namespace TestTraits\Trait;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP JSON Test Trait.
 */
trait HttpJsonTestTrait
{
    use ArrayTestTrait;
    use HttpTestTrait;

    /**
     * Create a JSON request.
     *
     * @param string $method The HTTP method
     * @param string|UriInterface $uri The URI
     * @param array|null $data The json data
     *
     * @return ServerRequestInterface
     */
    protected function createJsonRequest(
        string $method,
        string|UriInterface $uri,
        ?array $data = null
    ): ServerRequestInterface {
        $request = $this->createRequest($method, $uri);

        if ($data !== null) {
            $request->getBody()->write((string)json_encode($data));
        }

        return $request->withHeader('Content-Type', 'application/json');
    }

    /**
     * Verify that the specified array is an exact match for the returned JSON.
     *
     * @param array $expected The expected array
     * @param ResponseInterface $response The response
     *
     * @return void
     */
    protected function assertJsonData(array $expected, ResponseInterface $response): void
    {
        $data = $this->getJsonData($response);

        $this->assertSame($expected, $data);
    }

    /**
     * Get JSON response as array.
     *
     * @param ResponseInterface $response The response
     *
     * @return array The data
     */
    protected function getJsonData(ResponseInterface $response): array
    {
        $actual = (string)$response->getBody();
        $this->assertJson($actual);

        return (array)json_decode($actual, true);
    }

    /**
     * Verify JSON response.
     *
     * @param ResponseInterface $response The response
     *
     * @return void
     */
    protected function assertJsonContentType(ResponseInterface $response): void
    {
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Verify that the specified array is an exact match for the returned JSON.
     *
     * @param mixed $expected The expected value
     * @param string $path The array path
     * @param ResponseInterface $response The response
     *
     * @return void
     */
    protected function assertJsonValue(mixed $expected, string $path, ResponseInterface $response)
    {
        $this->assertSame($expected, $this->getArrayValue($this->getJsonData($response), $path));
    }

    /**
     * Asserts that the given JSON response data partially matches the expected JSON data.
     *
     * This function filters the response data to only contain the keys that are present in the expected JSON data.
     * It then compares the filtered response data with the expected JSON data using the assertEquals method.
     *
     * @param array $expectedJsonData The expected JSON data. This can be a two-dimensional array and the keys
     *                                of the subarrays are compared with the response data.
     * @param array $responseJsonData The actual JSON data received in the response i.e. $this->getJsonData($response)
     *
     * @return void
     */
    protected function assertPartialJsonData(array $expectedJsonData, array $responseJsonData): void
    {
        // Filter the response data to only contain the keys that are present in the expected json data
        $filteredResponseData = [];

        foreach ($expectedJsonData as $key => $expectedValue) {
            // If the expected array is an array and the corresponding key in the response data is also an array,
            // then array from the json response is added to the filtered response data.
            if (is_array($expectedValue) && isset($responseJsonData[$key]) && is_array($responseJsonData[$key])) {
                // Only the keys that are present in the expected subarray are added (array_intersect_key).
                $filteredResponseData[$key] = array_intersect_key($responseJsonData[$key], $expectedValue);
            } else {
                // If the value of the expected json array is not an array or the corresponding key in the response
                // data is not an array, the corresponding key from the response data is added to the
                // filtered response data. (if it exists)
                $filteredResponseData[$key] = $responseJsonData[$key] ?? null;
            }
        }

        $this->assertEquals($expectedJsonData, $filteredResponseData);
    }
}
