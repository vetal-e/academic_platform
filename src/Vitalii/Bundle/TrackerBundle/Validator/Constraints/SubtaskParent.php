<?php

namespace Vitalii\Bundle\TrackerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class SubtaskParent extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Issue: {{ issue_code }}, type: {{ issue_type }}, parent: {{ parent_code }}. Only subtasks can have a parent issue';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'tracker_subtask_parent_validator';
    }
}
