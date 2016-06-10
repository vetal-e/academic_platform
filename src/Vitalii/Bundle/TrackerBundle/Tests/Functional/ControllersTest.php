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
        $this->initClient(array(), $this->generateBasicAuthHeader(), $force = true);
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadIssueData']);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/tracker/issue/');
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
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

    public function testUpdate()
    {
        /** @var Issue $issue */
        $issue = $this->doctrine->getRepository('VitaliiTrackerBundle:Issue')->findOneByCode('test-02');

        $crawler = $this->client->request('GET', '/tracker/issue/update/' . $issue->getId());
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("test-02", $crawler->html());
    }

    public function testCreate()
    {
        $this->client->request('GET', '/tracker/issue/create');
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testAddSubtaskInStory()
    {
        // Issue of type story
        /** @var Issue $issue */
        $issueStory = $this->doctrine->getRepository('VitaliiTrackerBundle:Issue')->findOneByCode('test-01');

        $this->client->request('GET', '/tracker/issue/subtask/' . $issueStory->getId());
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testAddSubtaskInTask()
    {
        // Issue of type task
        /** @var Issue $issue */
        $issueTask = $this->doctrine->getRepository('VitaliiTrackerBundle:Issue')->findOneByCode('test-02');

        $this->client->request('GET', '/tracker/issue/subtask/' . $issueTask->getId());
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 403);
    }

    public function testDelete()
    {
        /** @var Issue $issue */
        $issue = $this->doctrine->getRepository('VitaliiTrackerBundle:Issue')->findOneByCode('test-02');

        $crawler = $this->client->request('GET', '/tracker/issue/delete/' . $issue->getId());
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 302);
    }
}
