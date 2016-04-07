<?php

namespace Support3w\JsonApiTransportService\Service;

interface JsonApiTransportService
{
    /**
     * @param $url
     * @param null $property
     * @param bool $encodingType
     * @return mixed
     */
    public function get($url, $property = null, $encodingType = false);

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function post($url, $data);

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function put($url, $data);
}