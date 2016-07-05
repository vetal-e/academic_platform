<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional\Manager;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
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

    public function testGetTypeChoices()
    {
        $typeChoices = $this->issueManager->getTypeChoices();
        $typeClassName = ExtendHelper::buildEnumValueClassName('issue_type');

        $this->assertEquals(3, count($typeChoices));
        $this->assertContainsOnlyInstancesOf($typeClassName, $typeChoices);
    }

    public function testGetTypeChoicesDontHaveSubtask()
    {
        $typeChoices = $this->issueManager->getTypeChoices();
        $typeIds = [];
        $typeIds[] = $typeChoices[0]->getId();
        $typeIds[] = $typeChoices[1]->getId();
        $typeIds[] = $typeChoices[2]->getId();

        $this->assertNotContains('subtask', $typeIds);
    }

    public function testGetLatestIssue()
    {
        $issueManagerReflection = new \ReflectionClass('Vitalii\Bundle\TrackerBundle\Manager\IssueManager');
        $getLatestIssueMethod = $issueManagerReflection->getMethod('getLatestIssue');
        $getLatestIssueMethod->setAccessible(true);

        $latestIssue = $getLatestIssueMethod->invoke($this->issueManager);

        $this->assertInstanceOf('Vitalii\Bundle\TrackerBundle\Entity\Issue', $latestIssue);
    }

    public function testGetCachedCode()
    {
        $issueManagerReflection = new \ReflectionClass('Vitalii\Bundle\TrackerBundle\Manager\IssueManager');
        $getCachedCodeMethod = $issueManagerReflection->getMethod('getCachedCode');
        $getCachedCodeMethod->setAccessible(true);

        $cachedCode = $getCachedCodeMethod->invokeArgs($this->issueManager, ['cachedcode']);

        $this->assertInstanceOf('Vitalii\Bundle\TrackerBundle\Entity\IssueCodesCache', $cachedCode);
    }
}
