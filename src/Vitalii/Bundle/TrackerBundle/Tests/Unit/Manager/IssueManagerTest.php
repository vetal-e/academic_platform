<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Unit\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\NoteBundle\Entity\Note;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;
use Vitalii\Bundle\TrackerBundle\Manager\IssueManager;

class IssueManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var IssueManager $issueManager */
    protected $issueManager;

    /** @var Issue|\PHPUnit_Framework_MockObject_MockObject $issue */
    protected $issue;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        /** @var Registry|\PHPUnit_Framework_MockObject_MockObject $doctrine */
        $doctrine = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->method('getManager')->willReturn($em);
        /** @var TokenStorage|\PHPUnit_Framework_MockObject_MockObject $tokenStorage */
        $tokenStorage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage');

        $this->issueManager = new IssueManager($doctrine, $tokenStorage);

        $this->issue = $this->getMock('Vitalii\Bundle\TrackerBundle\Entity\Issue', [
            'addCollaborators',
            'getReporter',
            'getAssignee',
            'setUpdatedAt',
        ]);
    }

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
        if ($hasReporter) {
            /** @var User|\PHPUnit_Framework_MockObject_MockObject $reporter */
            $reporter = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
            $this->issue->method('getReporter')->willReturn($reporter);
        }

        if ($hasAssignee) {
            /** @var User|\PHPUnit_Framework_MockObject_MockObject $assignee */
            $assignee = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
            $this->issue->method('getAssignee')->willReturn($assignee);
        }

        $this->issue->expects($this->exactly($expected))->method('addCollaborators');

        $this->issueManager->addCollaboratorsFromIssue($this->issue);
    }

    public function testAddCollaboratorsFromNote()
    {
        /** @var Note|\PHPUnit_Framework_MockObject_MockObject $note */
        $note = $this->getMock('Oro\Bundle\NoteBundle\Entity\Note');
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $noteAuthor */
        $noteAuthor = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $note->method('getTarget')->willReturn($this->issue);
        $note->method('getOwner')->willReturn($noteAuthor);

        $this->issue->expects($this->once())->method('addCollaborators');

        $this->issueManager->addCollaboratorsFromNote($note);
    }

    public function testAddCollaboratorsFromNoteDoNothingOnEmptyTarget()
    {
        /** @var Note|\PHPUnit_Framework_MockObject_MockObject $note */
        $note = $this->getMock('Oro\Bundle\NoteBundle\Entity\Note');
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $noteAuthor */
        $noteAuthor = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $note->method('getTarget')->willReturn(null);
        $note->method('getOwner')->willReturn($noteAuthor);

        $this->issue->expects($this->never())->method('addCollaborators');

        $this->issueManager->addCollaboratorsFromNote($note);
    }

    public function testUpdateDateOnNote()
    {
        /** @var Note|\PHPUnit_Framework_MockObject_MockObject $note */
        $note = $this->getMock('Oro\Bundle\NoteBundle\Entity\Note');

        $note->method('getTarget')->willReturn($this->issue);

        $this->issue->expects($this->once())->method('setUpdatedAt');

        $this->issueManager->updateDateOnNote($note);
    }

    public function testUpdateDateOnNoteDoNothingOnEmptyTarget()
    {
        /** @var Note|\PHPUnit_Framework_MockObject_MockObject $note */
        $note = $this->getMock('Oro\Bundle\NoteBundle\Entity\Note');

        $note->method('getTarget')->willReturn(null);

        $this->issue->expects($this->never())->method('setUpdatedAt');

        $this->issueManager->updateDateOnNote($note);
    }
}
