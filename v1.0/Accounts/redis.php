<?php

require 'Predis/Autoloader.php';

Predis\Autoloader::register();

$client = new Predis\Client();
$client->set('foo', 'bar');
$value = $client->get('foo');

// Parameters passed using a named array:
$client = new Predis\Client([
    'scheme' => 'tcp',
    'host'   => '10.0.0.1',
    'port'   => 6379,
]);