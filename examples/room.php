<?php
require '../vendor/autoload.php';
$config = require('config.inc.php');
error_reporting(-1);

use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Exception\Stream\StanzasErrorException;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\Room\RequestRoomConfigForm;
use Fabiang\Xmpp\Protocol\Room\Room;
use Fabiang\Xmpp\Protocol\Room\RoomConfig;
use Fabiang\Xmpp\Protocol\Room\RoomPresence;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('xmpp.log', Logger::DEBUG));

$address = $config['connectionType'] . '://' . $config['host'] . ':' . $config['port'];

$room = 'new-room';
$password = '';

$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($config['login'])
    ->setPassword($config['password'])
    ->setVerifyPeer($config['verifyPeer']);

$client = new Client($options);

$client->connect();
try {
    $request = new RoomPresence(
        $config['login'] . '@' . $config['host'],
        $config['conference'],
        $room
    );

    $client->send($request);

} catch (StanzasErrorException $e) {
    if ($e->getCode() != StanzasErrorException::ERROR_CONFLICT) {
        fwrite(STDOUT, $e->getMessage() . PHP_EOL);
        exit;
    } else {
        fwrite(STDOUT, 'Room is already exists.' . PHP_EOL);
    }
}

if ($client->getOptions()->getRoom()->isJustCreated()) {
    fwrite(STDOUT, 'Room presence has been created.' . PHP_EOL);
}

if (!$client->getOptions()->getRoom()->isOwner()) {
    fwrite(STDOUT, 'You are not owner of this room, so forbidden for configuring it.' . PHP_EOL);
    exit;
}


try {
    $requestForm = new RequestRoomConfigForm(
        $config['login'] . '@' . $config['host'],
        $config['conference'],
        $room
    );
    $client->send($requestForm);
    $form = $client->getOptions()->getForm();

    /**
     * @see https://xmpp.org/extensions/xep-0045.html#createroom-reserved
     */
    $form->setFieldValue('muc#roomconfig_roomname', 'New cool room');
    $form->setFieldValue('muc#roomconfig_roomdesc', 'Some description...');
    // no public logging. before turn this option on you must check that logging is enabled
    $form->setFieldValue('muc#roomconfig_enablelogging', Room::CONFIG_NO);
    // only owner can change name of this room
    $form->setFieldValue('muc#roomconfig_changesubject', Room::CONFIG_NO);
    // members can invite other users?
    $form->setFieldValue('muc#roomconfig_allowinvites', Room::CONFIG_NO);
    // allow private messages in this room
    //$form->setFieldValue('muc#roomconfig_allowpm', 'anyone');
    // max users limit.
    $options = $form->getFieldOptions('muc#roomconfig_maxusers');
    $max_users = empty($options) ? 100 : end($options);
    $form->setFieldValue('muc#roomconfig_maxusers', $max_users);


    // hidden room
    $form->setFieldValue('muc#roomconfig_publicroom', Room::CONFIG_NO);

    $form->setFieldValue('muc#roomconfig_persistentroom', Room::CONFIG_YES);
    $form->setFieldValue('muc#roomconfig_moderatedroom', Room::CONFIG_NO);
    // Only invited users can become members
    $form->setFieldValue('muc#roomconfig_membersonly', Room::CONFIG_YES);
    // password protected
    $form->setFieldValue('muc#roomconfig_passwordprotectedroom', Room::CONFIG_NO);
    //$form->setFieldValue('muc#roomconfig_roomsecret', '');

    // Who May Discover Real JIDs?
    $form->setFieldValue('muc#roomconfig_whois', 'anyone');
    // how many message keeps in history
    $form->setFieldValue('muc#maxhistoryfetch', 100);

    try {
        $roomConfig = new RoomConfig($config['login'] . '@' . $config['host'],
            $config['conference'],
            $room,
            $form);
        $client->send($roomConfig);

        fwrite(STDOUT, 'Room success configured' . PHP_EOL);
    } catch (StanzasErrorException $e) {
        fwrite(STDOUT, 'Failed to configure room!' . PHP_EOL);
        fwrite(STDOUT, $e->getMessage() . PHP_EOL);
    }

} catch (StanzasErrorException $e) {
    fwrite(STDOUT, 'Failed to create room!' . PHP_EOL);
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
}
$client->disconnect();