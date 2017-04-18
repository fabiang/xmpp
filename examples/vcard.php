<?php
require '../vendor/autoload.php';
$config = require('config.inc.php');
error_reporting(-1);

use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Exception\Stream\CommandErrorException;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\User\VCardPresence;
use Fabiang\Xmpp\Protocol\User\VCardUpdate;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('xmpp');
$fh = fopen('xmpp.log', 'a+');
$logger->pushHandler(new StreamHandler($fh, Logger::DEBUG));

$address = $config['connectionType'] . '://' . $config['host'] . ':' . $config['port'];

$login = 'testuser';
$password = 'test-password';


$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($login)
    ->setPassword($password)
    ->setVerifyPeer($config['verifyPeer']);

$client = new Client($options);

$client->connect();

$vCard = new VCardUpdate($login);
$vCard->setProperty('NICKNAME', 'iCoolVan 22222')
    ->setProperty('FAMILY', 'Ivanov')
    ->setProperty('GIVEN', 'Ivan')
    ->setProperty('MIDDLE', 'Ivanovich')
    ->setProperty('EMAIL', 'info@personal-site.com');


$vCard->setProperty('PHOTO', 'avatar.png');
$image_hash = $vCard->getImageId();

$vCard->setProperty('URL', 'https://personal-site.com');
try {
    $client->send($vCard);
    // tell other users that we have update vCard
    $presence = new VCardPresence($image_hash);
    $client->send($presence);
    fwrite(STDOUT, 'vCard was updated.' . PHP_EOL);
} catch (CommandErrorException $e) {
    fwrite(STDOUT, 'Failed to update user vCard!' . PHP_EOL);
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
}
$client->disconnect();
