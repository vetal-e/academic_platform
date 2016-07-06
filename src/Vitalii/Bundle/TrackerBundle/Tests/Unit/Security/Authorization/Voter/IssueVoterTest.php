<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Unit\Security\Authorization\Voter;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;
use Vitalii\Bundle\TrackerBundle\Security\Authorization\Voter\IssueVoter;

class IssueVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionClass
     */
    protected $issueVoterReflection;

    /**
     * @var IssueVoter
     */
    protected $issueVoter;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->issueVoterReflection = new \ReflectionClass(
            'Vitalii\Bundle\TrackerBundle\Security\Authorization\Voter\IssueVoter'
        );

        $this->issueVoter = new IssueVoter();
    }

    public function testGetSupportedAttributes()
    {
        $getSupportedAttributesMethod = $this->issueVoterReflection->getMethod('getSupportedAttributes');
        $getSupportedAttributesMethod->setAccessible(true);

        $supportedAttributes = $getSupportedAttributesMethod->invoke($this->issueVoter);

        $this->assertContains('subtask', $supportedAttributes);
    }

    public function testGetSupportedClasses()
    {
        $getSupportedClassesMethod = $this->issueVoterReflection->getMethod('getSupportedClasses');
        $getSupportedClassesMethod->setAccessible(true);

        $supportedClasses = $getSupportedClassesMethod->invoke($this->issueVoter);

        $this->assertContains('Vitalii\Bundle\TrackerBundle\Entity\Issue', $supportedClasses);
    }

    /**
     * @return array[]
     */
    public function isGrantedProvider()
    {
        return [
            ['subtask', 'story', true],
            ['subtask', 'task', false],
            ['task', 'story', false],
            ['task', 'task', false],
        ];
    }

    /**
     * @dataProvider isGrantedProvider
     * @param string $attribute
     * @param string $type
     * @param boolean $expected
     */
    public function testIsGranted($attribute, $type, $expected)
    {
        $isGrantedMethod = $this->issueVoterReflection->getMethod('isGranted');
        $isGrantedMethod->setAccessible(true);

        $issue = $this->getMock('Vitalii\Bundle\TrackerBundle\Entity\Issue', ['getType']);
        $issueTypeClass = ExtendHelper::buildEnumValueClassName('issue_type');
        $issueType = $this->getMock($issueTypeClass, ['getId']);

        $issueType->method('getId')->willReturn($type);
        $issue->method('getType')->willReturn($issueType);

        $result = $isGrantedMethod->invokeArgs($this->issueVoter, [$attribute, $issue]);

        $this->assertEquals($expected, $result);
    }

    public function testIsClassSupported()
    {
        $isClassSupported = $this->issueVoterReflection->getMethod('isClassSupported');
        $isClassSupported->setAccessible(true);

        $object = new Issue();

        $result = $isClassSupported->invokeArgs($this->issueVoter, [$object]);

        $this->assertEquals(true, $result);
    }

    public function testIsClassNotSupported()
    {
        $isClassSupported = $this->issueVoterReflection->getMethod('isClassSupported');
        $isClassSupported->setAccessible(true);

        $object = new User();

        $result = $isClassSupported->invokeArgs($this->issueVoter, [$object]);

        $this->assertEquals(false, $result);
    }

    public function testVote()
    {
        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $issue = $this->getMock('Vitalii\Bundle\TrackerBundle\Entity\Issue', ['getType']);
        $issueTypeClass = ExtendHelper::buildEnumValueClassName('issue_type');
        $issueType = $this->getMock($issueTypeClass, ['getId']);

        $issueType->method('getId')->willReturn('story');
        $issue->method('getType')->willReturn($issueType);

        $attributes = ['subtask'];

        $result = $this->issueVoter->vote($token, $issue, $attributes);

        $this->assertEquals(1, $result);
    }

    public function testVoteAccessDenied()
    {
        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $issue = $this->getMock('Vitalii\Bundle\TrackerBundle\Entity\Issue', ['getType']);
        $issueTypeClass = ExtendHelper::buildEnumValueClassName('issue_type');
        $issueType = $this->getMock($issueTypeClass, ['getId']);

        $issueType->method('getId')->willReturn('task');
        $issue->method('getType')->willReturn($issueType);

        $attributes = ['subtask'];

        $result = $this->issueVoter->vote($token, $issue, $attributes);

        $this->assertEquals(-1, $result);
    }

    public function testVoteNotObject()
    {
        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $object = 'text';

        $attributes = ['subtask'];

        $result = $this->issueVoter->vote($token, $object, $attributes);

        $this->assertEquals(0, $result);
    }

    public function testVoteNotIssue()
    {
        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $object = new User();

        $attributes = ['subtask'];

        $result = $this->issueVoter->vote($token, $object, $attributes);

        $this->assertEquals(0, $result);
    }
}
