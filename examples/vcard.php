<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require('config.inc.php');

error_reporting(-1);

use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Exception\Stream\StanzasErrorException;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\User\VCardPresence;
use Fabiang\Xmpp\Protocol\User\VCardUpdate;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$address = $config['connectionType'] . '://' . $config['host'] . ':' . $config['port'];

$login = 'testuser';
$password = '123456';


$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($login)
    ->setPassword($password);

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

$vCard = new VCardUpdate($login);
$vCard->setProperty('NICKNAME', 'iCoolVan')
    ->setProperty('FAMILY', 'Ivanov')
    ->setProperty('GIVEN', 'Ivan')
    ->setProperty('MIDDLE', 'Ivanovich')
    ->setProperty('EMAIL', 'info@personal-site.com');


$vCard->setProperty('PHOTO', __DIR__ . '/avatar.png')
    ->setImageUrl("https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_150x54dp.png");
$image_hash = $vCard->getImageId();

$vCard->setProperty('URL', 'https://personal-site.com');
try {
    $client->send($vCard);
    // tell other users that we have update vCard
    $presence = new VCardPresence($image_hash);
    $client->send($presence);
    fwrite(STDOUT, 'vCard was updated.' . PHP_EOL);
} catch (StanzasErrorException $e) {
    fwrite(STDOUT, 'Failed to update user vCard!' . PHP_EOL);
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
}
$client->disconnect();
