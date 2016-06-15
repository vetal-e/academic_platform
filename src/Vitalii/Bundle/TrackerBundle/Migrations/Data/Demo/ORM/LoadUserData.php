<?php

namespace Vitalii\Bundle\TrackerBundle\Migrations\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
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
    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine')->getManager();
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $userAdmin = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        if (empty($userAdmin)) {
            $userAdmin = new User();
            $userAdmin->setUsername('admin');
            $userAdmin->setEmail('admin@example.com');
            $userAdmin->setFirstName('John');
            $userAdmin->setLastName('Doe');
            $userAdmin->setPassword('admin');
            $userAdmin->setOrganization($organization);
            $userAdmin->addRole(User::ROLE_ADMINISTRATOR);
            $em->persist($userAdmin);
        }

        $userVitaly = new User();
        $userVitaly->setUsername('vitaly');
        $userVitaly->setEmail('vitaly@example.com');
        $userVitaly->setFirstName('Vitaly');
        $userVitaly->setLastName('Eryomenko');
        $userVitaly->setPassword('vitaly');
        $userVitaly->setOrganization($organization);
        $em->persist($userVitaly);

        $userSergey = new User();
        $userSergey->setUsername('sergey');
        $userSergey->setEmail('sergey@example.com');
        $userSergey->setFirstName('Sergey');
        $userSergey->setLastName('Zhuravel');
        $userSergey->setPassword('sergey');
        $userSergey->setOrganization($organization);
        $em->persist($userSergey);

        $this->setReference($userVitaly->getUsername(), $userVitaly);
        $this->setReference($userSergey->getUsername(), $userSergey);

        $em->flush();
    }
}