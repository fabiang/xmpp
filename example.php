<?php

require 'vendor/autoload.php';
error_reporting(-1);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$hostname       = 'localhost';
$port           = 5222;
$connectionType = 'tcp';
$address        = "$connectionType://$hostname:$port";

$options = new Options($address);
$options->setLogger($logger);

$username = 'xmpp';
$password = 'test';

$client = new Client($options);

$options->getImplementation()->registerListener(new Fabiang\Xmpp\EventListener\Authentication($username, $password));

//$client->connect();
$client->send(new Fabiang\Xmpp\Protocol\Message());

$client->disconnect();
