<?php

namespace Vitalii\Bundle\TrackerBundle\Mailer;

use Oro\Bundle\UserBundle\Mailer\BaseProcessor;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

class Processor extends BaseProcessor
{
    const ISSUE_ASSIGNED_TEMPLATE_NAME = 'issue_assigned_email';
    const ISSUE_STATE_CHANGED_TEMPLATE_NAME = 'issue_state_changed_email';

    public function sendIssueAssignedEmail(Issue $issue)
    {
        $user = $issue->getAssignee();

        return $this->getEmailTemplateAndSendEmail(
            $user,
            static::ISSUE_ASSIGNED_TEMPLATE_NAME,
            ['entity' => $user, 'issue' => $issue]
        );
    }

    public function sendIssueStateChangedEmails(Issue $issue)
    {
        $collaborators = $issue->getCollaborators();

        foreach ($collaborators as $user) {
            $this->getEmailTemplateAndSendEmail(
                $user,
                static::ISSUE_STATE_CHANGED_TEMPLATE_NAME,
                ['entity' => $user, 'issue' => $issue]
            );
        }
    }
}
