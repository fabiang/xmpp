<?php

namespace TraderBot\Service;

use Fabiang\Xmpp\Options;

class SocksProxy extends SocketClient
{
    /** @var string */
    private $realAddress;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    public function __construct(Options $options)
    {
        $contextOptions = $options->getContextOptions();
        $contextOptions['ssl']['verify_peer_name'] = false;
        $addr = explode('@', ltrim('tcp://', $options->getSocksProxyAddress()));
        if (count($addr) > 1) {
            $socksAddr = $addr[1];
            list($this->username, $this->password) = explode(':', $addr[0]);
        } else {
            $socksAddr = $addr[0];
        }
        parent::__construct($socksAddr, $contextOptions);
        $this->realAddress = ltrim('tcp://', $options->getAddress());
    }

    public function connect($timeout = 30, $persistent = false)
    {
        parent::connect($timeout, $persistent);
        $methods = chr(0);
        if ($this->username) {
            $methods .= chr(2);
        }
        $this->write(chr(5).chr(strlen($methods)).$methods);
        $version = ord($this->read(1));
        $method = ord($this->read(1));
        if ($version !== 5) {
            throw new \Exception("Wrong SOCKS5 version: $version");
        }
        if ($method === 2) {
            $this->write(chr(1).chr(strlen($this->username)).$this->username.chr(strlen($this->password)).$this->password);

            $version = ord($this->read(1));
            if ($version !== 1) {
                throw new \Exception("Wrong authorized SOCKS version: $version");
            }
            $result = ord($this->read(1));
            if ($result !== 0) {
                throw new \Exception("Wrong authorization status: $result");
            }
        } elseif ($method !== 0) {
            throw new \Exception("Wrong method: $method");
        }
        list($address, $port) = explode(':', $this->realAddress);
        $payload  = pack('C5', 0x05, 0x01, 0x00, 0x03, strlen($address)).$address;
        $payload .= pack('n', $port);
        $this->write($payload);

        $version = ord($this->read(1));
        if ($version !== 5) {
            throw new \Exception("Wrong SOCKS5 version after CONNECT: $version");
        }
        $rep = ord($this->read(1));
        if ($rep !== 0) {
            throw new \Exception("Wrong SOCKS5 rep after CONNECT: $rep");
        }
        $rsv = ord($this->read(1));
        if ($rsv !== 0) {
            throw new \Exception("Wrong socks5 final RSV after CONNECT: $rsv");
        }
        switch (ord($this->read(1))) {
            case 1:
                $ip = inet_ntop($this->read(4));
                break;
            case 4:
                $ip = inet_ntop($this->read(16));
                break;
            case 3:
                $ip = $this->read(ord($this->read(1)));
                break;
        }
        $port = unpack('n', $this->read(2))[1];
    }
}
