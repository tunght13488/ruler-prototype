<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DefaultControllerTest.
 */
class DefaultControllerTest extends WebTestCase
{
    // public function testIndex()
    // {
    //     $client = static::createClient();
    //
    //     $crawler = $client->request('GET', '/');
    //
    //     $this->assertEquals(200, $client->getResponse()->getStatusCode());
    //     $this->assertContains('Welcome to Symfony', $crawler->filter('#container h1')->text());
    // }

    /**
     * @dataProvider \AppBundle\Tests\Controller\DefaultControllerTest::getCheckData
     *
     * @param int    $sender
     * @param string $postcode
     * @param array  $expected
     */
    public function testCheck($sender, $postcode, $expected)
    {

        $user = $this->getMock(User::class);

        $userRepository = $this->getMockBuilder(UserRepository::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $userRepository->expects($this->any())
                       ->method('findOneBy')
                       ->will($this->returnValueMap([
                           [['facebookSenderId' => 123], null, null],
                           [['facebookSenderId' => 234], null, $user],
                       ]));

        $entityManager = $this->getMockBuilder(ObjectManager::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $entityManager->expects($this->any())
                      ->method('getRepository')
                      ->with($this->equalTo('AppBundle:User'))
                      ->will($this->returnValue($userRepository));

        $client = static::createClient();
        $client->getContainer()->set('doctrine.orm.default_entity_manager', $entityManager);

        $data = [
            'sender' => $sender,
            'postcode' => $postcode,
        ];

        $client->request('POST', '/', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), sprintf("Response code: %s. Content:\n%s", $response->getStatusCode(), $response->getContent()));
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $response->getContent());
    }

    /**
     * @return array
     */
    public function getCheckData()
    {
        return [
            [123, null, ['message' => 'show_age_check']],
            [234, '1234', ['message' => 'show_location']],
            [234, null, ['message' => 'show_generic']],
        ];
    }
}
