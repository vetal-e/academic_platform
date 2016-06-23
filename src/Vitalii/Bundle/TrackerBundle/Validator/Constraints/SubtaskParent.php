<?php

namespace Vitalii\Bundle\TrackerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class SubtaskParent extends Constraint
{
    /**
     * @var string
     */
    public $message = 'only_subtasks_can_have_parent_issue';

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
