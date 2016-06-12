<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

/**
 * @dbIsolation
 */
class IssueCollaboratorsTest extends WebTestCase
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
        /** @var Issue $issue */
        $issue = $this->doctrine->getRepository('VitaliiTrackerBundle:Issue')->findOneByCode('test-02');

        $crawler = $this->client->request('GET', '/tracker/issue/' . $issue->getId());
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        // Parent element for Collaborators header
        $crawler = $crawler->filterXPath('//h4[text() = "Collaborators"]/..');
        // TODO: cannot assert because the grid is not rendered
        $this->assertContains(self::AUTH_USER, $crawler->html());
    }
}
