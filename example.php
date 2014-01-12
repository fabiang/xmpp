<?php

require_once 'vendor/autoload.php';
error_reporting(-1);

use Fabiang\Xmpp\Connection\Socket;
use Fabiang\Xmpp\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$hostname       = 'localhost';
$port           = 5222;
$username       = 'xmpp';
$password       = 'test';
$connectionType = 'tcp';
$address        = "$connectionType://$hostname:$port";
$scheme         = 'tcp';

$connection = Socket::factory($address);

$client = new Client($connection, $logger);
$client->connect();

$client->registerListner(new Fabiang\Xmpp\EventListener\Stream());
//$client->registerListner(new Fabiang\Xmpp\EventListener\StartTls());
$client->registerListner(new Fabiang\Xmpp\EventListener\Authentication($username, $password));

$stream = new Fabiang\Xmpp\Protocol\Stream();
$stream->setTo('localhost');
$client->send($stream);

$client->disconnect();
