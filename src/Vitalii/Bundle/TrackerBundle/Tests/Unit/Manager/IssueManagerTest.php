<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Unit\Manager;

use Vitalii\Bundle\TrackerBundle\Manager\IssueManager;

class IssueManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array[]
     */
    public function issueCollaboratorsProvider()
    {
        return [
            'No collaborators' =>
                [false, false, 0],
            'Reporter as collaborator' =>
                [true, false, 1],
            'Assignee as collaborator' =>
                [false, true, 1],
            'Reporter and assignee as collaborators' =>
                [true, true, 2],
        ];
    }

    /**
     * @dataProvider issueCollaboratorsProvider
     */
    public function testAddCollaboratorsFromIssue($hasReporter, $hasAssignee, $expected)
    {
        $doctrine = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->method('getManager')->willReturn($em);
        $tokenStorage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage');

        $issueManager = new IssueManager($doctrine, $tokenStorage);

        $issue = $this->getMock('Vitalii\Bundle\TrackerBundle\Entity\Issue', ['addCollaborators', 'getReporter', 'getAssignee']);

        if ($hasReporter) {
            $reporter = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
            $issue->method('getReporter')->willReturn($reporter);
        }

        if ($hasAssignee) {
            $assignee = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
            $issue->method('getAssignee')->willReturn($assignee);
        }

        $issue->expects($this->exactly($expected))->method('addCollaborators');

        $issueManager->addCollaboratorsFromIssue($issue);
    }
}
