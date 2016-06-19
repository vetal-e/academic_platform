<?php

namespace Vitalii\Bundle\TrackerBundle\Migrations\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\NoteBundle\Entity\Note;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

class LoadNoteData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Vitalii\Bundle\TrackerBundle\Migrations\Demo\ORM\LoadIssueData'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine')->getManager();

        /** @var User $userAdmin */
        $userAdmin = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        /** @var User $userVitaly */
        $userVitaly = $this->getReference('vitaly');
        /** @var User $userSergey */
        $userSergey = $this->getReference('sergey');

        /** @var Issue $issueStory */
        $issueStory = $this->getReference('issueStory');
        /** @var Issue $issueSubtask1 */
        $issueSubtask1 = $this->getReference('issueSubtask1');
        /** @var Issue $issueSubtask2 */
        $issueSubtask2 = $this->getReference('issueSubtask2');
        /** @var Issue $issueTask */
        $issueTask = $this->getReference('issueTask');

        $note1 = new Note();
        $note1->setOwner($userAdmin);
        $note1->setOrganization($userAdmin->getOrganization());
        $note1->setMessage('This story will be broken to several subtasks');
        $em->persist($note1);
        $note1->setTarget($issueStory);

        $note2 = new Note();
        $note2->setOwner($userVitaly);
        $note2->setOrganization($userVitaly->getOrganization());
        $note2->setMessage('Do we have to complete all subtasks?');
        $em->persist($note2);
        $note2->setTarget($issueStory);

        $note3 = new Note();
        $note3->setOwner($userAdmin);
        $note3->setOrganization($userAdmin->getOrganization());
        $note3->setMessage('Sure you do');
        $em->persist($note3);
        $note3->setTarget($issueStory);

        $note4 = new Note();
        $note4->setOwner($userVitaly);
        $note4->setOrganization($userVitaly->getOrganization());
        $note4->setMessage('This task is very important');
        $em->persist($note4);
        $note4->setTarget($issueSubtask1);

        $note5 = new Note();
        $note5->setOwner($userSergey);
        $note5->setOrganization($userSergey->getOrganization());
        $note5->setMessage('Why is it?');
        $em->persist($note5);
        $note5->setTarget($issueSubtask1);

        $note6 = new Note();
        $note6->setOwner($userAdmin);
        $note6->setOrganization($userAdmin->getOrganization());
        $note6->setMessage('Because I say so!');
        $em->persist($note6);
        $note6->setTarget($issueSubtask1);

        $note7 = new Note();
        $note7->setOwner($userSergey);
        $note7->setOrganization($userSergey->getOrganization());
        $note7->setMessage('Seems everybody forgot about this task');
        $em->persist($note7);
        $note7->setTarget($issueTask);

        $em->flush();
    }
}
