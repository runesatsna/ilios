<?php

namespace Ilios\CoreBundle\Tests\Controller;

use FOS\RestBundle\Util\Codes;

/**
 * CurriculumInventoryAcademicLevel controller Test.
 * @package Ilios\CoreBundle\Test\Controller;
 */
class CurriculumInventoryAcademicLevelControllerTest extends AbstractControllerTest
{
    /**
     * @return array|string
     */
    protected function getFixtures()
    {
        return [
            'Ilios\CoreBundle\Tests\Fixture\LoadCurriculumInventoryAcademicLevelData',
            'Ilios\CoreBundle\Tests\Fixture\LoadCurriculumInventoryReportData',
            'Ilios\CoreBundle\Tests\Fixture\LoadCurriculumInventorySequenceBlockData'
        ];
    }

    /**
     * @return array|string
     */
    protected function getPrivateFields()
    {
        return [];
    }

    public function testGetCurriculumInventoryAcademicLevel()
    {
        $curriculumInventoryAcademicLevel = $this->container
            ->get('ilioscore.dataloader.curriculuminventoryacademiclevel')
            ->getOne()
        ;

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_curriculuminventoryacademiclevels',
                ['id' => $curriculumInventoryAcademicLevel['id']]
            )
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($curriculumInventoryAcademicLevel),
            json_decode($response->getContent(), true)['curriculumInventoryAcademicLevels'][0]
        );
    }

    public function testGetAllCurriculumInventoryAcademicLevels()
    {
        $this->createJsonRequest('GET', $this->getUrl('cget_curriculuminventoryacademiclevels'));
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize(
                $this->container
                    ->get('ilioscore.dataloader.curriculuminventoryacademiclevel')
                    ->getAll()
            ),
            json_decode($response->getContent(), true)['curriculumInventoryAcademicLevels']
        );
    }

    public function testPostCurriculumInventoryAcademicLevel()
    {
        $data = $this->container->get('ilioscore.dataloader.curriculuminventoryacademiclevel')
            ->create();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_curriculuminventoryacademiclevels'),
            json_encode(['curriculumInventoryAcademicLevel' => $postData])
        );

        $response = $this->client->getResponse();
        $headers  = [];

        $this->assertEquals(Codes::HTTP_CREATED, $response->getStatusCode(), $response->getContent());
        $this->assertEquals(
            $data,
            json_decode($response->getContent(), true)['curriculumInventoryAcademicLevels'][0],
            $response->getContent()
        );
    }

    public function testPostBadCurriculumInventoryAcademicLevel()
    {
        $invalidCurriculumInventoryAcademicLevel = $this->container
            ->get('ilioscore.dataloader.curriculuminventoryacademiclevel')
            ->createInvalid()
        ;

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_curriculuminventoryacademiclevels'),
            json_encode(['curriculumInventoryAcademicLevel' => $invalidCurriculumInventoryAcademicLevel])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testPutCurriculumInventoryAcademicLevel()
    {
        $data = $this->container
            ->get('ilioscore.dataloader.curriculuminventoryacademiclevel')
            ->getOne();

        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_curriculuminventoryacademiclevels',
                ['id' => $data['id']]
            ),
            json_encode(['curriculumInventoryAcademicLevel' => $postData])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($data),
            json_decode($response->getContent(), true)['curriculumInventoryAcademicLevel']
        );
    }

    public function testDeleteCurriculumInventoryAcademicLevel()
    {
        $curriculumInventoryAcademicLevel = $this->container
            ->get('ilioscore.dataloader.curriculuminventoryacademiclevel')
            ->getOne()
        ;

        $this->client->request(
            'DELETE',
            $this->getUrl(
                'delete_curriculuminventoryacademiclevels',
                ['id' => $curriculumInventoryAcademicLevel['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->client->request(
            'GET',
            $this->getUrl(
                'get_curriculuminventoryacademiclevels',
                ['id' => $curriculumInventoryAcademicLevel['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCurriculumInventoryAcademicLevelNotFound()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('get_curriculuminventoryacademiclevels', ['id' => '0'])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }
}
