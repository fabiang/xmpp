<?php

namespace TraderBot\Service;

use Fabiang\Xmpp\Options;

class SocksProxy extends SocketClient
{
    private $realAddress;

    public function __construct(Options $options)
    {
        $contextOptions = array_merge($options->getContextOptions(), [
            'ssl' => [
                'verify_peer_name' => false
            ]
        ]);
        parent::__construct($options->getSocksProxyAddress(), $contextOptions);
        $this->realAddress = ltrim('tcp://', $options->getAddress());
    }

    public function connect($timeout = 30, $persistent = false)
    {
        parent::connect($timeout, $persistent);
        $this->write(chr(5).chr(1).chr(0));
        $version = ord($this->read(1));
        $method = ord($this->read(1));
        if ($version !== 5) {
            throw new \Exception("Wrong SOCKS5 version: $version");
        }
        if ($method !== 0) {
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
