<?php

require __DIR__ . '/../vendor/autoload.php';
$config = require('config.inc.php');

error_reporting(-1);

use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\Message;
use Fabiang\Xmpp\Protocol\Presence;
use Fabiang\Xmpp\Protocol\Roster;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$hostname       = 'localhost';
$port           = 5222;
$connectionType = 'tcp';
$address        = $config['connectionType'] . '://' . $config['host'] . ':' . $config['port'];


$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($config['login'])
    ->setPassword($config['password']);

if ($config['verifyPeer'] === false) {
    $options->setContextOptions([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ]);
}

$client = new Client($options);

$client->connect();
$client->send(new Roster);
$client->send(new Presence);
$client->send(new Message);

$client->disconnect();
