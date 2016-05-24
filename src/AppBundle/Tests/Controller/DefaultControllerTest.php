<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DefaultControllerTest.
 */
class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Welcome to Symfony', $crawler->filter('#container h1')->text());
    }

    public function testCheck()
    {
        $client = static::createClient();

        $data = [
            'group' => 'customer',
            'points' => 42,
        ];

        $client->request('POST', '/', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode(['valid' => true, 'data' => $data]), $response->getContent());
    }
}
