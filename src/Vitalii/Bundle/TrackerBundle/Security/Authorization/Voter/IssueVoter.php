<?php

namespace Vitalii\Bundle\TrackerBundle\Security\Authorization\Voter;

use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

class IssueVoter extends AbstractVoter
{
    const SUBTASK = 'subtask';

    protected function getSupportedAttributes()
    {
        return array(self::SUBTASK);
    }

    protected function getSupportedClasses()
    {
        return array('Vitalii\Bundle\TrackerBundle\Entity\Issue');
    }

    /**
     * @param string $attribute
     * @param Issue $issue
     * @param User $user
     * @return bool
     */
    protected function isGranted($attribute, $issue, $user = null)
    {
        switch ($attribute) {
            case self::SUBTASK:
                if ($issue->getType()->getId() === 'story') {
                    return true;
                }
                break;
        }

        return false;
    }
}
