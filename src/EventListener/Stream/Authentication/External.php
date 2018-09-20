<?php
/**
 * Created by PhpStorm.
 * User: Alex alex.n@symfonyart.com
 * Date: 19.09.2018
 */

namespace Fabiang\Xmpp\EventListener\Stream\Authentication;

use Fabiang\Xmpp\EventListener\AbstractEventListener;

/**
 * Class External
 * @package Fabiang\Xmpp\EventListener\Stream\Authentication
 */
class External extends AbstractEventListener implements AuthenticationInterface
{
    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {

    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($username, $password)
    {
        $this->getConnection()->send(
            '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="EXTERNAL"/>'
        );
    }
}
