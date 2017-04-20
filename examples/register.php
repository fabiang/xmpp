<?php
require '../vendor/autoload.php';
$config = require('config.inc.php');
error_reporting(-1);

use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Exception\Stream\StanzasErrorException;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\User\ChangeUserPassword;
use Fabiang\Xmpp\Protocol\User\RegisterUser;
use Fabiang\Xmpp\Protocol\User\RequestChangePasswordForm;
use Fabiang\Xmpp\Protocol\User\RequestUserRegisterForm;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('xmpp.log', Logger::DEBUG));

$address = $config['connectionType'] . '://' . $config['host'] . ':' . $config['port'];

$newUser = 'testuser';
$newPassword = '123456';

$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($config['login'])
    ->setPassword($config['password'])
    ->setVerifyPeer($config['verifyPeer']);

$client = new Client($options);

$client->connect();
$request = new RequestUserRegisterForm($config['login'] . '@' . $config['host'], $config['host']);
$client->send($request);

$form = $client->getOptions()->getForm();
$user = new RegisterUser(
    $newUser . '@' . $config['host'],
    $newPassword,
    $config['login'] . '@' . $config['host'],
    $config['host'],
    $form
);

try {
    $client->send($user);
    print 'User is registered' . PHP_EOL;
} catch (StanzasErrorException $e) {
    /**
     * @see https://xmpp.org/extensions/xep-0086.html#sect-idm139696314152720
     */
    if ($e->getCode() == StanzasErrorException::ERROR_CONFLICT) { //  conflict
        fwrite(STDOUT, 'User already exists. Try to change password' . PHP_EOL);
        $request = new RequestChangePasswordForm(
            $config['login'] . '@' . $config['host'],
            $config['host']
        );

        try {
            $client->send($request);

            $form = $client->getOptions()->getForm();
            $user = new ChangeUserPassword(
                $newUser . '@' . $config['host'],
                $newPassword,
                $config['login'] . '@' . $config['host'],
                $config['host'],
                $form
            );
        } catch (StanzasErrorException $e) {
            fwrite(STDOUT, 'Failed to change password of user!' . PHP_EOL);
            fwrite(STDOUT, $e->getMessage() . PHP_EOL);
        }

    } else {
        fwrite(STDOUT, 'Failed to register user!' . PHP_EOL);
        fwrite(STDOUT, $e->getMessage() . PHP_EOL);
    }
}
$client->disconnect();