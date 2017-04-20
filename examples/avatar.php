<?php
require '../vendor/autoload.php';
$config = require('config.inc.php');
error_reporting(-1);

use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\User\Avatar;
use Fabiang\Xmpp\Protocol\User\AvatarMetadata;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('xmpp.log', Logger::DEBUG));

$address = $config['connectionType'] . '://' . $config['host'] . ':' . $config['port'];

$login = $config['login'];//'testuser';
$password = '123456';


$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($login)
    ->setPassword($config['password'])
    ->setVerifyPeer($config['verifyPeer']);

$client = new Client($options);

$client->connect();

$imagePath = 'avatar.png';

$avatar = new Avatar(
    $login . '@' . $config['host'],
    $imagePath
);

$url = '';
//$url = 'https://avatar.personal-site.com/64x64/testuser.png';


try {
    $client->send($avatar);
    // update avatar metadata
    $meta = new AvatarMetadata(
        $login . '@' . $config['host'],
        $imagePath,
        $url
    );
    $client->send($meta);
    fwrite(STDOUT, 'Avatar was updated.' . PHP_EOL);
} catch (Exception $e) {
    fwrite(STDOUT, 'Failed to update user avatar!' . PHP_EOL);
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
}
$client->disconnect();
