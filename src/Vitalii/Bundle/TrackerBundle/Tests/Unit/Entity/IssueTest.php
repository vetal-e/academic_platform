<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Unit\Entity;

use Vitalii\Bundle\TrackerBundle\Entity\Issue;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class IssueTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new Issue(), [
            ['id', 42],
            ['summary', 'some string'],
            ['code', 'some string'],
            ['description', 'some string'],
            ['reporter', new \Oro\Bundle\UserBundle\Entity\User()],
            ['assignee', new \Oro\Bundle\UserBundle\Entity\User()],
            ['parentIssue', new Issue()],
            ['childIssues', new ArrayCollection(), false],
            ['organization', new \Oro\Bundle\OrganizationBundle\Entity\Organization()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['updatedAtSet', 1]
        ]);
    }
}
