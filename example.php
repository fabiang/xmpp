<?php

require 'vendor/autoload.php';
error_reporting(-1);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;

use Fabiang\Xmpp\Protocol\Roster;
use Fabiang\Xmpp\Protocol\Presence;
use Fabiang\Xmpp\Protocol\Message;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$hostname       = 'localhost';
$port           = 5222;
$connectionType = 'tcp';
$address        = "$connectionType://$hostname:$port";

$username = 'xmpp';
$password = 'test';

$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($username)
    ->setPassword($password);

$client = new Client($options);

$client->connect();
$client->send(new Roster);
$client->send(new Presence);
$client->send(new Message);

$client->disconnect();
