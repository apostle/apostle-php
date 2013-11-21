<?php

namespace Apostle;

class TestCase extends \Guzzle\Tests\GuzzleTestCase
{
    private $client;

	public function setUp()
	{
		\Apostle::setup("abc", array("deliver" => false));
	}

    protected function client()
    {
        if (!$this->client)
            $this->client = $this->getServiceBuilder()->get('client');

        return $this->client;
    }

    protected function getOnlyMockedRequest($method = null, $path = null)
    {
        $requests = $this->getMockedRequests();
        $count = count($requests);

        if ($count != 1)
            $this->fail("Expected 1 HTTP request, got $count!");

        $request = $requests[0];

        if ($method && $path)
            $this->assertRequest($method, $path, $request);
        else if ($method || $path)
            $this->fail('$method and $path must both be present or null.');

        return $request;
    }

    protected function assertRequest($method, $path, $request = null)
    {
        if (!$request) $request = $this->getOnlyMockedRequest();
        $this->assertEquals($method, $request->getMethod());
        $this->assertEquals($path, $request->getResource());
    }

    protected function assertBearerToken($token, $request = null)
    {
        if (!$request) $request = $this->getOnlyMockedRequest();

        if (!($header = $request->getHeader('Authorization')))
            $this->fail("Missing Authorization header.");

        $this->assertEquals(
            'Bearer ' . $token,
            $header->__toString()
        );
    }

    protected function assertRequestJson($object, $request = null)
    {
        if (!$request) $request = $this->getOnlyMockedRequest();

        if (!($body = $request->getBody()))
            $this->fail('Missing request entity body.');

        $this->assertEquals(
            json_encode($object),
            $body->__toString()
        );
    }
}

