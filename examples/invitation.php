<?php
require '../vendor/autoload.php';
$config = require('config.inc.php');
error_reporting(-1);

use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Exception\Stream\StreamErrorException;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\Room\DirectInvite;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('xmpp.log', Logger::DEBUG));

$address = $config['connectionType'] . '://' . $config['host'] . ':' . $config['port'];

$room = 'new-room';
$password = '';
$newUser = 'testuser';

$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($config['login'])
    ->setPassword($config['password'])
    ->setVerifyPeer($config['verifyPeer']);

$client = new Client($options);

$client->connect();
$invitation = new DirectInvite(
    $config['login'] . '@' . $config['host'],
    $newUser . '@' . $config['host'],
    $room . '@' . $config['conference'],
    $password,
    'This is a cool party room! Join to it!'
);
try {
    $client->send($invitation);

    fwrite(STDOUT, 'Invitation sent' . PHP_EOL);
} catch (StreamErrorException $e) {
    fwrite(STDOUT, 'Invitation failed' . PHP_EOL);
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
}

$client->disconnect();