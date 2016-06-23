<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Unit;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Vitalii\Bundle\TrackerBundle\Security\Authorization\Voter\IssueVoter;

class SubtaskVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function issueProvider()
    {
        return [
            ['bug', 'bug', false],
            ['bug', 'task', false],
            ['bug', 'story', false],
            ['bug', 'subtask', false],

            ['task', 'bug', false],
            ['task', 'task', false],
            ['task', 'story', false],
            ['task', 'subtask', false],

            ['story', 'bug', false],
            ['story', 'task', false],
            ['story', 'story', false],
            ['story', 'subtask', false],

            ['subtask', 'bug', false],
            ['subtask', 'task', false],
            ['subtask', 'story', true],
            ['subtask', 'subtask', false],
        ];
    }

    /**
     * @dataProvider issueProvider
     */
    public function testIsGranted($attribute, $parentType, $expected)
    {
        $voter = new IssueVoter();
        $reflector = new \ReflectionClass('Vitalii\Bundle\TrackerBundle\Security\Authorization\Voter\IssueVoter');
        $method = $reflector->getMethod('isGranted');
        $method->setAccessible(true);

        $typeClassName = ExtendHelper::buildEnumValueClassName('issue_type');
        $type = $this->getMock($typeClassName, ['getId']);
        $type->method('getId')
            ->will($this->returnValue($parentType))
        ;

        $issue = $this->getMock('Vitalii\Bundle\TrackerBundle\Entity\Issue', ['getType']);
        $issue->method('getType')
            ->will($this->returnValue($type))
        ;

        $result = $method->invokeArgs($voter, [$attribute, $issue]);
        $this->assertEquals($expected, $result, "$attribute cannot be created in $parentType");
    }
}
