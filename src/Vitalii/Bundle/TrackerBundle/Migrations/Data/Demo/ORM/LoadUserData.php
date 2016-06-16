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
        $userManager = $this->container->get('oro_user.manager');

        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $userAdmin = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        if (empty($userAdmin)) {
            /** @var User $userAdmin */
            $userAdmin = $userManager->createUser();
            $userAdmin->setUsername('admin');
            $userAdmin->setEmail('admin@example.com');
            $userAdmin->setFirstName('John');
            $userAdmin->setLastName('Doe');
            $userAdmin->setPlainPassword('admin')
                ->setSalt('9u438eycmscgcc8wogscwwkk8kc8ks1');
            $userAdmin->setOrganization($organization);
            $organization->addUser($userAdmin);
            $userAdmin->addRole(User::ROLE_ADMINISTRATOR);
            $userManager->updateUser($userAdmin);
        }

        /** @var User $userVitaly */
        $userVitaly = $userManager->createUser();
        $userVitaly->setUsername('vitaly');
        $userVitaly->setEmail('vitaly@example.com');
        $userVitaly->setFirstName('Vitaly');
        $userVitaly->setLastName('Eryomenko');
        $userVitaly->setPlainPassword('vitaly')
            ->setSalt('9u438eycmscgcc8wogscwwkk8kc8ks2');
        $userVitaly->setOrganization($organization);
        $organization->addUser($userVitaly);
        $userManager->updateUser($userVitaly);

        /** @var User $userSergey */
        $userSergey = $userManager->createUser();
        $userSergey->setUsername('sergey');
        $userSergey->setEmail('sergey@example.com');
        $userSergey->setFirstName('Sergey');
        $userSergey->setLastName('Zhuravel');
        $userSergey->setPlainPassword('sergey')
            ->setSalt('9u438eycmscgcc8wogscwwkk8kc8ks3');
        $userSergey->setOrganization($organization);
        $organization->addUser($userSergey);
        $userManager->updateUser($userSergey);

        $this->setReference($userVitaly->getUsername(), $userVitaly);
        $this->setReference($userSergey->getUsername(), $userSergey);

        $em->flush();
    }
}