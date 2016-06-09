<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

/**
 * @dbIsolation
 */
class ControllersTest extends WebTestCase
{
    /**
     * @var Registry
     */
    protected $doctrine;

    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadIssueData']);
    }

    public function testView()
    {
        /** @var Issue $issue */
        $issue = $this->doctrine->getRepository('VitaliiTrackerBundle:Issue')->findOneByCode('test-02');

        $crawler = $this->client->request('GET', '/tracker/issue/' . $issue->getId());
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("test-02", $crawler->html());
    }

    public function testIndex()
    {
        $this->client->request('GET', '/tracker/issue/');
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }
}
