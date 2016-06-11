<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Command\GenerateWSSEHeaderCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

/**
 * @dbIsolation
 */
class IssueViewSectionsTest extends WebTestCase
{
    /**
     * @var Registry
     */
    protected $doctrine;

    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader(), $force = true);
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadIssueData']);

        $kernel = $this->createKernel();
        $kernel->boot();
        $application = new Application($kernel);
        $application->add(new GenerateWSSEHeaderCommand());

        $command = $application->find('oro:wsse:generate-header');
        $command->run();
//        $commandTester = new CommandTester($command);
//        $commandTester->execute(array('command' => $command->getName()));
    }

    public function testApi()
    {
        $command = $this->getApplication()->find('oro:wsse:generate-header');

        $arguments = array(
            'command' => 'oro:wsse:generate-header',
            'apiKey'  => 'a6adad840daf00f68758ad17bfd79c509595cb70',
        );

        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);
    }
}
