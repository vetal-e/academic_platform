<?php

namespace Vitalii\Bundle\TrackerBundle\Form\Type;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vitalii\Bundle\TrackerBundle\Form\EventListener\FieldSetListener;
use Vitalii\Bundle\TrackerBundle\Manager\IssueManager;

class IssueType extends AbstractType
{
    /**
     * @var IssueManager $issueManager
     */
    private $issueManager;

    /**
     * @var FieldSetListener $fieldSetListener
     */
    private $fieldSetListener;

    /**
     * @param IssueManager $issueManager
     * @param FieldSetListener $fieldSetListener
     */
    public function __construct(IssueManager $issueManager, FieldSetListener $fieldSetListener)
    {
        $this->issueManager = $issueManager;
        $this->fieldSetListener = $fieldSetListener;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('summary')
            ->add('description')
            ->add('assignee', null, [
                'label' => 'Assignee',
            ])
            ->add('type', 'entity', [
                    'class' => ExtendHelper::buildEnumValueClassName('issue_type'),
                    'choices' => $this->issueManager->getTypeChoices(),
                ])
            ->add('priority')
        ;

        $builder->addEventSubscriber($this->fieldSetListener);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Vitalii\Bundle\TrackerBundle\Entity\Issue',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tracker_issue';
    }
}
