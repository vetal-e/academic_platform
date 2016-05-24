<?php

namespace Vitalii\Bundle\TrackerBundle\Manager;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

class IssueManager
{
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
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
}