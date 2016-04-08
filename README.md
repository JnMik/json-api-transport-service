# Usage

	$redisClient = new Predis\Client([
    	'scheme' => 'tcp',
    	'host'   => REDIS_IP,
    	'port'   => REDIS_PORT,
	]);

	// 1 day
	$cacheKeyTTL = 86400;

	$forceUpdateCache = false;
	if(isset($_GET['force'])) {
    		$forceUpdateCache = true;
	}

	$app['json-api-transport.service'] = new GuzzleJsonApiTransportService($redisClient, $cacheKeyTTL, $forceUpdateCache);

	$response = $app['json-api-transport.service']->get($this->apiUrl . '?deleted=1');
	$response = $app['json-api-transport.service']->post($this->apiUrl, $params);
	$response = $app['json-api-transport.service']->put($this->apiUrl . "/" . $id, $params);
