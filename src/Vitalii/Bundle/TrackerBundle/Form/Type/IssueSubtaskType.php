<?php

namespace Vitalii\Bundle\TrackerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IssueSubtaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('summary')
            ->add('assignee', null, [
                'label' => 'Assignee',
            ])
            ->add('priority')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Vitalii\Bundle\TrackerBundle\Entity\Issue',
        ));
    }

    public function getName()
    {
        return 'tracker_issue_subtask';
    }
}
