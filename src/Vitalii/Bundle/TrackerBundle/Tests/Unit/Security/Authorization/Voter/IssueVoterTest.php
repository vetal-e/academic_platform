<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Unit\Security\Authorization\Voter;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
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
}
