<?php

/**
 * Records the requests sent by MailjetTrackingConsentApiClient and answers with canned responses
 */
class FakeMailjetTransport
{
    /** @var array[] [method, url, body] */
    public $requests = [];

    /** @var callable|null function ($method, $url, $body): array|null */
    public $responder;

    public function __invoke($method, $url, $body = null)
    {
        $this->requests[] = [$method, $url, $body];
        if ($this->responder) {
            $response = call_user_func($this->responder, $method, $url, $body);
            if ($response !== null) {
                return $response;
            }
        }

        if ($method === 'POST') {
            return [201, null];
        }

        return [200, json_decode('{"Count":0,"Data":[]}')];
    }

    /**
     * @param string $method
     * @return array[]
     */
    public function requestsFor($method)
    {
        return array_values(array_filter($this->requests, function ($request) use ($method) {
            return $request[0] === $method;
        }));
    }
}
