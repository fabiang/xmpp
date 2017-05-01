<?php
require '../vendor/autoload.php';
$config = require('config.inc.php');
error_reporting(-1);

use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Exception\Stream\StanzasErrorException;
use Fabiang\Xmpp\Exception\Stream\StreamErrorException;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\Pubsub\BookmarkItem;
use Fabiang\Xmpp\Protocol\Pubsub\PubsubGet;
use Fabiang\Xmpp\Protocol\Pubsub\PubsubSet;
use Fabiang\Xmpp\Protocol\Room\Membership;
use Fabiang\Xmpp\Protocol\Room\RequestRoomConfigForm;
use Fabiang\Xmpp\Protocol\Room\Room;
use Fabiang\Xmpp\Protocol\Room\RoomConfig;
use Fabiang\Xmpp\Protocol\Room\RoomPresence;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('xmpp.log', Logger::DEBUG));

$address = $config['connectionType'] . '://' . $config['host'] . ':' . $config['port'];

$room = 'test-room';
$password = '';
$newUser = 'testuser';
$newPassword = '123456';

$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($config['login'])
    ->setPassword($config['password'])
    ->setVerifyPeer($config['verifyPeer']);

$client = new Client($options);

$client->connect();
try {
    $presence = new RoomPresence(
        $config['login'] . '@' . $config['host'],
        $config['conference'],
        $room
    );

    $client->send($presence);

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
} else if ($client->getOptions()->getRoom()->isOwner()) {
    fwrite(STDOUT, 'Room is already exists. But you are owner of the group. Continue configuring...' . PHP_EOL);
}


if (!$client->getOptions()->getRoom()->isOwner()) {
    // this case is often occurs when persistent room is already exists
    // TODO: check room affiliation of current user
    fwrite(STDOUT, 'You are not owner of this room, so forbidden to configuring it.' . PHP_EOL);
}


try {
    $presenceForm = new RequestRoomConfigForm(
        $config['login'] . '@' . $config['host'],
        $config['conference'],
        $room
    );
    $client->send($presenceForm);
    $form = $client->getOptions()->getForm();

    if (!$client->getOptions()->getRoom()) {
        $client->getOptions()->setRoom(new Room());
    }
    $client->getOptions()->getRoom()->setName('New cool room');

    /**
     * @see https://xmpp.org/extensions/xep-0045.html#createroom-reserved
     **/
    $form->setFieldValue('muc#roomconfig_roomname', $client->getOptions()->getRoom()->getName());
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


        try {

            $member = new Membership(
                $config['login'] . '@' . $config['host'],
                $client->getOptions()->getRoom()->getJid(),
                Membership::AFFILIATION_MEMBER,
                $newUser . '@' . $config['host']
            );
            $client->send($member);

            fwrite(STDOUT, 'The ' . $newUser . '@' . $config['host'] . ' now is member of the room ' . $room . PHP_EOL);
        } catch (StanzasErrorException $e) {
            fwrite(STDOUT, 'Can\'t add member' . PHP_EOL);
            fwrite(STDOUT, $e->getMessage() . PHP_EOL);
        }
    } catch (StanzasErrorException $e) {
        fwrite(STDOUT, 'Failed to configure room!' . PHP_EOL);
        fwrite(STDOUT, $e->getMessage() . PHP_EOL);
    }

} catch (StanzasErrorException $e) {
    fwrite(STDOUT, 'Failed to create room!' . PHP_EOL);
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
}
$client->disconnect();



// the room is not in user roster
// so add it into bookmarks
fwrite(STDOUT, 'Login as ' . $newUser . PHP_EOL);

$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($newUser)
    ->setPassword($newPassword)
    ->setVerifyPeer($config['verifyPeer']);

$client = new Client($options);


$client->connect();

try {
    // firstly, we must get all bookmarks
    $pubsub = new PubsubGet(
        $newUser . '@' . $config['host'],
        '',// set this option empty for bookmarks, in other cases this option must by something like this pubsub.xmpp.stie.com
        PubsubGet::NODE_BOOKMARKS
    );

    $client->send($pubsub);

} catch (StanzasErrorException $e) {
    if ($e->getCode() == StanzasErrorException::ERROR_ITEM_NOT_FOUND) {
        fwrite(STDOUT, 'Bookmarks is empty' . PHP_EOL);
    } else {
        fwrite(STDOUT, $e->getMessage() . PHP_EOL);
        exit;
    }

}

try {
    // secondly, we must check that bookmark does not exists
    $exists = false;
    /** @var BookmarkItem $item */
    foreach ($client->getOptions()->getUser()->getPubsubs(PubsubGet::NODE_BOOKMARKS) as $item) {
        if ($item->getJid() == $room . '@' . $config['conference']) {
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        // add bookmark to a list
        $bookmark = new BookmarkItem(
            $room . '@' . $config['conference'],
            'New cool room',
            true,
            $newUser
        );
        $client->getOptions()->getUser()->addBookmark($bookmark);

        $bookmarkSet = new PubsubSet(
            $newUser . '@' . $config['host'],
            PubsubSet::NODE_BOOKMARKS);

        $bookmarkSet->setItems(
            $client->getOptions()->getUser()->getPubsubs(PubsubGet::NODE_BOOKMARKS)
        );

        $client->send($bookmarkSet);


        fwrite(STDOUT, 'Bookmark for ' . $room . '@' . $config['conference'] . ' is created.' . PHP_EOL);
    } else {
        fwrite(STDOUT, 'Bookmark ' . $room . '@' . $config['conference'] . ' exists.' . PHP_EOL);
    }
} catch (StreamErrorException $e) {
    fwrite(STDOUT, 'Can\'t create bookmark for ' . $room . '@' . $config['conference'] . '.' . PHP_EOL);
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
}
$client->disconnect();