<?php

namespace Vitalii\Bundle\TrackerBundle\Manager;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\NoteBundle\Entity\Note;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

class IssueManager
{
    private $doctrine;
    private $token;

    public function __construct(Registry $doctrine, TokenStorage $tokenStorage)
    {
        $this->doctrine = $doctrine;
        $this->token = $tokenStorage->getToken();
    }

    public function addCollaboratorsFromIssue(Issue $issue)
    {
        $collaborators = [];
        $collaborators[] = $issue->getReporter();
        $collaborators[] = $issue->getAssignee();

        foreach ($collaborators as $collaborator) {
            $issue->addCollaborators($collaborator);
        }

        $this->doctrine->getManager()->flush();
    }

    public function addCollaboratorsFromNote(Note $note)
    {
        $issue = $note->getTarget();

        $issue->addCollaborators($this->token->getUser());

        $this->doctrine->getManager()->flush();
    }

    public function updateDateOnNote(Note $note)
    {
        /** @var Issue $issue */
        $issue = $note->getTarget();
        $issue->setUpdatedAt(new \DateTime('now'));

        $this->doctrine->getManager()->flush();
    }
}
