<?php

/**
 * Header bag shared by the Swift_Message and Symfony Email doubles
 */
class FakeHeaders
{
    public $headers = [];

    public function has($name)
    {
        return isset($this->headers[$name]);
    }

    public function remove($name)
    {
        unset($this->headers[$name]);
    }

    public function addTextHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }
}

/**
 * Same recipient format as Swift_Message: [email => name]
 */
class FakeSwiftMessage
{
    private $headers;
    private $to;
    private $bcc;

    public function __construct(array $to, array $bcc = [])
    {
        $this->headers = new FakeHeaders();
        $this->to = $to;
        $this->bcc = $bcc;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getCc()
    {
        return null;
    }

    public function getBcc()
    {
        return $this->bcc;
    }
}

class FakeAddress
{
    private $address;

    public function __construct($address)
    {
        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }
}

/**
 * Same recipient format as Symfony\Component\Mime\Email: Address[]
 */
class FakeSymfonyEmail
{
    private $headers;
    private $to;

    public function __construct(array $to)
    {
        $this->headers = new FakeHeaders();
        $this->to = array_map(function ($email) {
            return new FakeAddress($email);
        }, $to);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getCc()
    {
        return [];
    }

    public function getBcc()
    {
        return [];
    }
}
