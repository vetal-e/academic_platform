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
            'getCode',
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

    /**
     * @return array[]
     */
    public function generateCodeProvider()
    {
        return [
            'No previous issue and no cached code' => [null, null, null],
            'No previous issue and cached code' => [null, 15, null],
            'Previous issue and no cached code' => ['latestissuecode-1', null, 'latestissuecode-2'],
            'Previous issue and cached code' => ['latestcachedcode-1', 15, 'latestcachedcode-16'],
            'Previous issue without number and no cached code' => ['latestcode', null, 'latestcode-1'],
            'Previous issue without number and cached code' => ['latestcode', 42, 'latestcode-43'],
        ];
    }

    /**
     * @dataProvider generateCodeProvider
     * @param null|string $latestIssueCode
     * @param null|number $latestCachedCode
     * @param null|string $expected
     */
    public function testGenerateCode($latestIssueCode, $latestCachedCode, $expected)
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

        /** @var IssueManager|\PHPUnit_Framework_MockObject_MockObject $mockIssueManager */
        $mockIssueManager = $this->getMockBuilder('Vitalii\Bundle\TrackerBundle\Manager\IssueManager')
            ->setMethods(['getLatestIssue', 'getCachedCode'])
            ->setConstructorArgs([$doctrine, $tokenStorage])
            ->getMock();

        if ($latestIssueCode) {
            $this->issue->method('getCode')->willReturn($latestIssueCode);
            $mockIssueManager->method('getLatestIssue')->willReturn($this->issue);
        } else {
            $mockIssueManager->method('getLatestIssue')->willReturn(null);
        }

        if ($latestCachedCode) {
            $cachedCode = $this->getMock('Vitalii\Bundle\TrackerBundle\Entity\IssueCodesCache');
            $cachedCode->method('getNumber')->willReturn($latestCachedCode);
            $mockIssueManager->method('getCachedCode')->willReturn($cachedCode);
        } else {
            $mockIssueManager->method('getCachedCode')->willReturn(null);
        }

        $code = $mockIssueManager->generateCode();

        $this->assertEquals($expected, $code);
    }
}
