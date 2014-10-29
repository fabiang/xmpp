# fabiang/xmpp

[![Latest Stable Version](https://poser.pugx.org/fabiang/xmpp/v/stable.svg)](https://packagist.org/packages/fabiang/xmpp) [![Total Downloads](https://poser.pugx.org/fabiang/xmpp/downloads.svg)](https://packagist.org/packages/fabiang/xmpp) [![Latest Unstable Version](https://poser.pugx.org/fabiang/xmpp/v/unstable.svg)](https://packagist.org/packages/fabiang/xmpp) [![License](https://poser.pugx.org/fabiang/xmpp/license.svg)](https://packagist.org/packages/fabiang/xmpp)  
[![Build Status](https://travis-ci.org/fabiang/xmpp.png?branch=master)](https://travis-ci.org/fabiang/xmpp) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/fabiang/xmpp/badges/quality-score.png?s=2605ad2bc987ff8501b8f749addff43ec1ac7098)](https://scrutinizer-ci.com/g/fabiang/xmpp/) [![Code Coverage](https://scrutinizer-ci.com/g/fabiang/xmpp/badges/coverage.png?s=cec78be78925c90569743c3265f7fe7d1fa1f2cd)](https://scrutinizer-ci.com/g/fabiang/xmpp/) [![Dependency Status](https://gemnasium.com/fabiang/xmpp.png)](https://gemnasium.com/fabiang/xmpp) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/a535cd82-788d-4506-803e-02ede44a9e74/mini.png)](https://insight.sensiolabs.com/projects/a535cd82-788d-4506-803e-02ede44a9e74)

Library for XMPP protocol connections (Jabber) for PHP.

## SYSTEM REQUIREMENTS

- PHP >= 5.3.3
- psr/log
- psr/log-implementation - like monolog/monolog for logging (optional)

## LICENCE

BSD-2-Clause. See the [LICENCE](LICENCE.md).

## INSTALLATION

New to Composer? Read the [introduction](https://getcomposer.org/doc/00-intro.md#introduction). Add the following to your composer file:

    {
        "require": {
            "fabiang/xmpp": "*"
        }
    }

## DOCUMENTATION

This library uses an object to hold options:

    use Fabiang\Xmpp\Options;
    $options = new Options($address);
    $options->setUsername($username)
        ->setPassword($password);

The server address must be in the format `tcp://myjabber.com:5222`.  
If the server supports TLS the connection will automatically be encrypted.

You can also pass a PSR-2-compatible object to the options object:

    $options->setLogger($logger)

The client manages the connection to the Jabber server and requires the options object:

    use Fabiang\Xmpp\Client;
    $client = new Client($options);
    // optional connect manually
    $client->connect();

For sending data you just need to pass a object that implements `Fabiang\Xmpp\Protocol\ProtocolImplementationInterface`:

    use Fabiang\Xmpp\Protocol\Roster;
    use Fabiang\Xmpp\Protocol\Presence;
    use Fabiang\Xmpp\Protocol\Message;

    // fetch roster list; users and their groups
    $client->send(new Roster);
    // set status to online
    $client->send(new Presence);

    // send a message to another user
    $message = new Message;
    $message->setMessage('test')
        ->setTo('nickname@myjabber.com')
    $client->send($message);

    // join a channel
    $channel = new Presence;
    $channel->setTo('channelname@conference.myjabber.com')
        ->setNickName('mynick');
    $client->send($channel);

    // send a message to the above channel
    $message = new Message;
    $message->setMessage('test')
        ->setTo('channelname@conference.myjabber.com')
        ->setType(Message::TYPE_GROUPCHAT);
    $client->send($message);

After all you should disconnect:

    $client->disconnect();

## DEVELOPING

If you like this library and you want to contribute, make sure the unit-tests and integration tests are running.
Composer will help you to install the right version of PHPUnit and [Behat](http://behat.org/).

    composer install --dev

After that:

    ./bin/phpunit -c tests
    ./bin/behat --config=tests/behat.yml --strict

New features should allways tested with Behat.

## TODO
    
- Better integration of channels
- Factory method for server addresses
- Add support von vCard
- improve documentation
