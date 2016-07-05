<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional\Manager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Vitalii\Bundle\TrackerBundle\Manager\IssueManager;

/**
 * @dbIsolation
 */
class IssueManagerTest extends WebTestCase
{
    /** @var IssueManager $issueManager */
    protected $issueManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadIssueData']);

        self::bootKernel();
        $this->issueManager = static::$kernel->getContainer()->get('tracker.issue.manager');
    }

    public function testGetCollaboratorsChoices()
    {
        $collaboratorsChoices = $this->issueManager->getCollaboratorsChoices();
        $this->assertEquals(2, count($collaboratorsChoices));
        $this->assertArraySubset(
            [
                'admin',
                'user2',
            ],
            array_values($collaboratorsChoices)
        );
    }
}
