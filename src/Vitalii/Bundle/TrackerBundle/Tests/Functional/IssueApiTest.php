<?php

namespace Vitalii\Bundle\TrackerBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Tests\Functional\ApiTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Command\GenerateWSSEHeaderCommand;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

/**
 * @dbIsolation
 */
class IssueViewSectionsTest extends ApiTestCase
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * {@inheritdoc}
     */
    protected function getRequestType()
    {
        return new RequestType([RequestType::REST, RequestType::JSON_API]);
    }

    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader(), $force = true);
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadIssueData']);
        $this->loadFixtures(['Vitalii\Bundle\TrackerBundle\Tests\Functional\DataFixtures\LoadUserData']);

//        parent::setUp();
    }

    public function testApi()
    {
        $this->client->request(
            'GET',
            '/api/trackerissues',
            [],
            [],
            array_replace(
                $this->generateWsseAuthHeader(),
//                $this->generateWSSEHeaders(),
                ['CONTENT_TYPE' => 'application/vnd.api+json']
            )
        );
        $response = $this->client->getResponse();

        $this->assertApiResponseStatusCodeEquals($response, 200, 'issue', 'get list');
    }

    private function generateWSSEHeaders()
    {
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'WSSE profile="UsernameToken"',
            'HTTP_X-WSSE' => '',
        ];

        /** @var User $user */
        $user = $this->doctrine->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        $userApi = $this->doctrine->getRepository('OroUserBundle:UserApi')->findOneBy(
            ['apiKey' => '211dd8446a6d2c6f0517882f1d175f00407f00f3']
        );
        $created = date('c');
        $prefix = gethostname();
        $nonce  = base64_encode(substr(md5(uniqid($prefix . '_', true)), 0, 16));
        $salt   = '';

        /** @var MessageDigestPasswordEncoder $encoder */
        $encoder        = $this->getContainer()->get('escape_wsse_authentication.encoder.wsse_secured');
        $passwordDigest = $encoder->encodePassword(
            sprintf(
                '%s%s%s',
                base64_decode($nonce),
                $created,
                $userApi->getApiKey()
            ),
            $salt
        );

        $headers['HTTP_X-WSSE'] = sprintf(
            'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $user->getUsername(),
            $passwordDigest,
            $nonce,
            $created
        );

        var_dump($headers);

        return $headers;
    }
}
