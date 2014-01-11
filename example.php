<?php

require_once 'vendor/autoload.php';

use Fabiang\Xmpp\Connection\Socket;
use Fabiang\Xmpp\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$hostname       = 'localhost'; //getenv('XMPP_HOSTNAME');
$port           = 5222; //getenv('XMPP_PORT');
$username       = 'xmpp'; //getenv('XMPP_USERNAME');
$password       = 'test'; //getenv('XMPP_PASSWORD');
$connectionType = 'tcp'; //getenv('XMPP_CONNECTION') ? : 'tcp';
$address        = "$connectionType://$hostname:$port";
$scheme         = 'tcp';

$connection = Socket::factory($address);
$client     = new Client($connection, $logger);
$client->connect();

$client->registerListner(new Fabiang\Xmpp\EventListener\Stream());
$client->registerListner(new Fabiang\Xmpp\EventListener\Authentication());

$stream = new Fabiang\Xmpp\Protocol\Stream();
$stream->setTo('localhost');
$client->send($stream);

$client->disconnect();
