<?php
require '../vendor/autoload.php';
$config = require('config.inc.php');
error_reporting(-1);

use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Exception\Stream\StreamErrorException;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\Presence;
use Fabiang\Xmpp\Protocol\Roster;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('xmpp.log', Logger::DEBUG));

$address = $config['connectionType'] . '://' . $config['host'] . ':' . $config['port'];

$newUser = 'testuser';
$newPassword = '123456';

$options1 = new Options($address);
$options1->setLogger($logger)
    ->setUsername($config['login'])
    ->setPassword($config['password'])
    ->setVerifyPeer($config['verifyPeer']);

$options2 = new Options($address);
$options2->setLogger($logger)
    ->setUsername($newUser)
    ->setPassword($newPassword)
    ->setVerifyPeer($config['verifyPeer']);

$client1 = new Client($options1);

$client1->connect();

// 0. check subscription state from roster
$roster = new Roster();
try {
    $client1->send($roster);
    $userList = $client1->getOptions()->getUsers();
    if (!empty($userList)) {
        foreach ($userList as $user) {
            if ($user->getJid() == $newUser . '@' . $config['host']) {
                fwrite(STDOUT, 'We have subscription for user ' .
                    $newUser . '@' . $config['host'] . '.' .
                    PHP_EOL);

                if ($user->isSubscribed()) {
                    fwrite(STDOUT, 'End.' . PHP_EOL);
                    $client1->disconnect();
                    exit;
                }
            }
        }
        fwrite(STDOUT, 'We are not completely subscribed to user ' . $newUser . '@' . $config['host'] . PHP_EOL);
    } else {
        fwrite(STDOUT, 'Roster is empty!' . PHP_EOL);
    }
} catch (StreamErrorException $e) {
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
    exit;
}


// 1. send subscription request
$presence = new Presence(1, $newUser . '@' . $config['host'], Presence::TYPE_SUBSCRIBE);
try {
    $client1->send($presence);
    fwrite(STDOUT, 'Subscribe request to ' . $newUser . '@' . $config['host'] . ' is sent.' . PHP_EOL);
} catch (StreamErrorException $e) {
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
    exit;
}

sleep(1);

$client2 = new Client($options2);

$client2->connect();

// 2. send subscription request from sender 2
$presence = new Presence(1, $config['login'] . '@' . $config['host'], Presence::TYPE_SUBSCRIBE);
try {
    $client2->send($presence);
    // 3. set auto approve for subscribed request
    $presence = new Presence(1, $config['login'] . '@' . $config['host'], Presence::TYPE_SUBSCRIBED);
    $client2->send($presence);
    fwrite(STDOUT, 'Subscribe request to ' . $config['login'] . '@' . $config['host'] . ' is sent.' . PHP_EOL);
} catch (StreamErrorException $e) {
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
    exit;
}

sleep(1);

// 4. approve subscription from sender 1
$presence = new Presence(1, $newUser . '@' . $config['host'], Presence::TYPE_SUBSCRIBED);
try {
    $client1->send($presence);
    fwrite(STDOUT, 'Subscribe request from ' . $newUser . '@' . $config['host'] . ' is approved.' . PHP_EOL);
} catch (StreamErrorException $e) {
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
    exit;
}
sleep(1);


$client1->disconnect();
$client2->disconnect();