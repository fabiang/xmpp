<?php

require 'vendor/autoload.php';
error_reporting(-1);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Updivision\Xmpp\Options;
use Updivision\Xmpp\Client;

use Updivision\Xmpp\Protocol\Roster;
use Updivision\Xmpp\Protocol\Presence;
use Updivision\Xmpp\Protocol\Message;

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
