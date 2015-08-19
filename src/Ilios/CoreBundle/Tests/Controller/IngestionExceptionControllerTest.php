<?php

namespace Ilios\CoreBundle\Tests\Controller;

use FOS\RestBundle\Util\Codes;

/**
 * IngestionException controller Test.
 * @package Ilios\CoreBundle\Test\Controller;
 */
class IngestionExceptionControllerTest extends AbstractControllerTest
{
    /**
     * @return array|string
     */
    protected function getFixtures()
    {
        $fixtures = parent::getFixtures();
        return array_merge($fixtures, [
            'Ilios\CoreBundle\Tests\Fixture\LoadIngestionExceptionData',
        ]);
    }

    /**
     * @return array|string
     */
    protected function getPrivateFields()
    {
        return [
        ];
    }

    public function testGetIngestionException()
    {
        $exception = $this->container
            ->get('ilioscore.dataloader.ingestionexception')
            ->getOne()
        ;

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_ingestionexception',
                ['id' => $exception['id']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($exception),
            json_decode($response->getContent(), true)['ingestionExceptions'][0]
        );
    }

    public function testGetAllIngestionExceptions()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('cget_ingestionexception'),
            null,
            $this->getAuthenticatedUserToken()
        );
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize(
                $this->container
                    ->get('ilioscore.dataloader.ingestionexception')
                    ->getAll()
            ),
            json_decode($response->getContent(), true)['ingestionExceptions']
        );
    }


    public function testIngestionExceptionNotFound()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('get_ingestionexception', ['id' => '0']),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }
}
