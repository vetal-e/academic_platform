<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Tests\Functional\ApiTestCase;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

/**
 * @dbIsolation
 */
class IssueApiTest extends ApiTestCase
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * {@inheritdoc}
     */
    protected function getRequestType()
    {
        return new RequestType([RequestType::REST, RequestType::JSON_API]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadIssueData']);

        parent::setUp();
    }

    public function testIssueApiList()
    {
        $this->client->request(
            'GET',
            '/api/trackerissues',
            [],
            [],
            array_replace(
                $this->generateWsseAuthHeader(),
                ['CONTENT_TYPE' => 'application/vnd.api+json']
            )
        );
        $response = $this->client->getResponse();

        $this->assertApiResponseStatusCodeEquals($response, 200, 'issue', 'get list');
    }

    public function testIssueApiSingle()
    {
        /** @var Issue $issue */
        $issue = $this->doctrine->getRepository('VitaliiTrackerBundle:Issue')->findOneByCode('test-02');

        $this->client->request(
            'GET',
            '/api/trackerissues/' . $issue->getId(),
            [],
            [],
            array_replace(
                $this->generateWsseAuthHeader(),
                ['CONTENT_TYPE' => 'application/vnd.api+json']
            )
        );
        $response = $this->client->getResponse();

        $this->assertApiResponseStatusCodeEquals($response, 200, 'issue', 'get list');
    }
}
