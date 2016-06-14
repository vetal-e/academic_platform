<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\NoteBundle\Entity\Note;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

/**
 * @dbIsolation
 */
class IssueNoteTest extends WebTestCase
{
    /**
     * @var Registry
     */
    protected $doctrine;

    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadIssueData']);
    }

    public function testCollaborators()
    {
        /** @var Issue $issue */
        $issue = $this->doctrine->getRepository('VitaliiTrackerBundle:Issue')->findOneByCode('test-01');
        $user = $issue->getReporter();

        $em = $this->doctrine->getManager();

        sleep(2);

        $note = new Note();
        $note->setOwner($user);
        $note->setOrganization($user->getOrganization());
        $note->setMessage('Test issue update on adding note');
        $note->setTarget($issue);

        $em->persist($note);
        $em->flush();

        $this->assertGreaterThan($issue->getCreatedAt()->getTimestamp(), $issue->getUpdatedAt()->getTimestamp());
    }
}
