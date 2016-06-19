<?php

namespace Vitalii\Bundle\TrackerBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

class FieldSetListener implements EventSubscriberInterface
{
    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var Issue $issue */
        $issue = $event->getData();

        // Editing subtask
        if (!empty($issue->getParentIssue())) {
            $form->remove('type');
        }

        // Editing story that have subtasks
        if (!$issue->getChildIssues()->isEmpty()) {
            $form->remove('type');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData'
        ];
    }
}
