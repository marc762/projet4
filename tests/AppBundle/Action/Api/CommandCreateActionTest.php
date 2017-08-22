<?php


namespace Test\AppBundle\Action\Api;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommandCreateActionTest extends WebTestCase
{
    public function testCreateCommandIsValid()
    {
        $client = static::createClient();
        $jsonContent = '{
            "type": "Journée",
            "email": "marc.arnoult@hotmail.fr",
            "entryAt": "2017-09-09",
            "tickets": 
                [{
                  "first_name": "marc",
                  "last_name": "Do lugar",
                  "birthday": "1991-08-09",
                  "country": "France",
                  "entryAt": "2017-09-09",
                  "reduction": false
                },{
                  "first_name": "marc",
                  "last_name": "marc",
                  "birthday": "1991-08-09",
                  "country": "France",
                  "reduction": false,
                  "entryAt": "2017-09-09"
                }]
        }';

        $client->request('POST', '/api/command', [], [], [], $jsonContent);
        $this->assertJson($client->getResponse()->getContent());
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testCreateCommandIsNotValid()
    {
        $client = static::createClient();
        $jsonContent = '{
            "type": "Journée",
            "email": "marc.arnoult@hotmail.fr",
            "entryAt": "2017-09-09",
            "tickets": 
                [{
                  "first_name": "",
                  "last_name": "Do lugar",
                  "birthday": "1991-08-09",
                  "country": "France",
                  "entryAt": "2017-09-09",
                  "reduction": false
                },{
                  "first_name": "marc",
                  "last_name": "marc",
                  "birthday": "1991-08-09",
                  "country": "France",
                  "reduction": false,
                  "entryAt": "2017-09-09"
                }]
        }';

        $client->request('POST', '/api/command', [], [], [], $jsonContent);
        $this->assertJson($client->getResponse()->getContent());
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }
}