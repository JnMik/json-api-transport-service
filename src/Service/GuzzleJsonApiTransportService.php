<?php

namespace Support3w\JsonApiTransportService\Service;

use GuzzleHttp\Client;
use Predis\Client as RedisClient;
use Predis\ClientInterface;

class GuzzleJsonApiTransportService implements JsonApiTransportService
{

    /**
     * @var RedisClient
     */
    protected $cacheClient;

    /**
     * @var Integer
     */
    protected $cacheKeyTtl;
    /**
     * @var bool
     */
    private $forceUpdateCache;

    /**
     * @param ClientInterface $cacheClient
     * @param null $cacheKeyTtl
     * @param bool $forceUpdateCache
     */
    public function __construct(ClientInterface $cacheClient = null, $cacheKeyTtl = null, $forceUpdateCache = false)
    {
        $this->cacheClient = $cacheClient;
        $this->cacheKeyTtl = $cacheKeyTtl;
        $this->forceUpdateCache = $forceUpdateCache;
    }

    /**
     * @param $url
     * @param null $property return a specific property of response data
     * @param bool $encodingType
     * @return mixed
     * @throws \Exception
     */
    public function get($url, $property = null, $encodingType = false)
    {

        if (!$this->forceUpdateCache) {

            try {
                // return cache value if available
                if (!is_null($this->cacheClient)) {
                    $keyExist = $this->cacheClient->exists($url);
                    if ($keyExist) {
                        return unserialize($this->cacheClient->get($url));
                    }
                }
            } catch (\Exception $e) {
                // redis server is having problem
                throw new \Exception('Cache client exception : ' . $e->getMessage());
            }

        }

        $httpClient = new Client();
        $request = $httpClient->createRequest('GET', $url);
        $request->getQuery()->setEncodingType($encodingType);
        $httpClientResponse = $httpClient->send($request);
        $jsonResponse = json_decode($httpClientResponse->getBody()->getContents());

        if (is_null($property)) {
            $this->addResponseToCache($url, $jsonResponse);
            return $jsonResponse;
        }

        $this->addResponseToCache($url, $jsonResponse);
        return $jsonResponse->$property;
    }

    public function post($url, $data)
    {
        $httpClient = new Client();
        $response = $httpClient->post($url, ['body' => json_encode($data)]);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param $url
     * @param $jsonResponse
     * @throws \Exception
     */
    private function addResponseToCache($url, $jsonResponse)
    {

        try {
            // save response into cache
            if (!is_null($this->cacheClient)) {
                if (is_null($this->cacheKeyTtl)) {
                    $this->cacheClient->set($url, serialize($jsonResponse));
                } else {
                    $this->cacheClient->setex($url, $this->cacheKeyTtl, serialize($jsonResponse));
                }
            }
        } catch (\Exception $e) {
            // redis server is having problem
            throw new \Exception('Cache client exception : ' . $e->getMessage());
        }

    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function put($url, $data)
    {
        $httpClient = new Client();
        $response = $httpClient->put($url, ['body' => json_encode($data)]);
        return json_decode($response->getBody()->getContents());
    }


}