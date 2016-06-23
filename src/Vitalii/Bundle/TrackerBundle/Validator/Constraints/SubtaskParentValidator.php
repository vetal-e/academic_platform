<?php

namespace Vitalii\Bundle\TrackerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SubtaskParentValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($issue, Constraint $constraint)
    {
        if (!empty($issue->getParentIssue()) and $issue->getType()->getId() !== 'subtask') {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ issue_code }}' => $issue->getCode(),
                    '{{ issue_type }}' => $issue->getType()->getName(),
                    '{{ parent_code }}' => $issue->getParentIssue()->getCode(),
                ]
            );
        }
    }
}
