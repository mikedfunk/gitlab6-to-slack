<?php

/**
 * Specification unit test for MikeFunk\Gitlab6ToSlack\Controllers\WebHookController.
 *
 * @package GitLab6ToSlack
 * @license MIT License <http://opensource.org/licenses/mit-license.html>
 */

namespace spec\MikeFunk\Gitlab6ToSlack\Controllers;

use Dotenv;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\Response as GuzzleResponse;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * WebHookControllerSpec.
 *
 * @author Michael Funk <mike@mikefunk.com>
 */
class WebHookControllerSpec extends ObjectBehavior
{

    /**
     * fake repository name for to update
     *
     * @var string
     */
    private $repositoryName = 'test_repository';

    /**
     * fake slack url endpoint
     *
     * @var string
     */
    protected $slackUrl = 'http://test_slack_url';

    /**
     * test contructor
     *
     * @test
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param GuzzleHttp\Client $guzzleClient
     * @param Mustache_Engine $mustacheEngine
     * @param Mustache_Loader_FilesystemLoader $fileSystemLoader
     * @return void
     */
    public function let(
        Request $request,
        GuzzleClient $guzzleClient,
        Mustache_Engine $mustacheEngine,
        Mustache_Loader_FilesystemLoader $fileSystemLoader
    ) {
        // assign a fake post parameter bag to $request->request
        $parameters = [
            'payload' => json_encode(
                ['repository' => ['name' => $this->repositoryName]]
            )
        ];
        $request->request = new ParameterBag($parameters);
        $this->beConstructedWith(
            $request,
            $guzzleClient,
            $mustacheEngine,
            $fileSystemLoader
        );
    }

    /**
     * it_is_initializable.
     */
    public function it_is_initializable()
    {
        $this->shouldHaveType(
            'MikeFunk\Gitlab6ToSlack\Controllers\WebHookController'
        );
    }

    /**
     * it_should_fail_if_slack_url_is_not_set
     *
     * @test
     * @return void
     */
    public function it_should_fail_if_slack_url_is_not_set()
    {
        $this->shouldThrow('RuntimeException')->during('indexAction');
    }

    /**
     * it_should_fail_if_payload_is_not_json
     *
     * @test
     * @return void
     */
    public function it_should_fail_if_payload_is_not_json(Request $request)
    {
        // make getenv() work with our fake slack url
        Dotenv::setEnvironmentVariable('SLACK_URL', $this->slackUrl);

        // assign a fake invalid post parameter bag to $request->request
        $parameters = ['payload' => 'NOT VALID JSON'];
        $request->request = new ParameterBag($parameters);

        $this->shouldThrow('UnexpectedValueException')->during('indexAction');
    }

    /**
     * it_should_fail_without_the_right_post_data
     *
     * @test
     * @return void
     */
    public function it_should_fail_without_the_right_post_data(Request $request)
    {
        // make getenv() work with our fake slack url
        Dotenv::setEnvironmentVariable('SLACK_URL', $this->slackUrl);

        // assign a fake invalid post parameter bag to $request->request
        $parameters = ['payload' => json_encode(['wrong' => 'structure'])];
        $request->request = new ParameterBag($parameters);

        $this->shouldThrow('ErrorException')->during('indexAction');
    }

    /**
     * it_should_hit_the_slack_api_when_receiving_a_gitlab_post.
     *
     * @test
     * @param GuzzleHttp\Client $guzzleClient
     * @param GuzzleHttp\Message\Response $guzzleResponse
     * @param Mustache_Engine $mustacheEngine
     * @param Mustache_Loader_FilesystemLoader $fileSystemLoader
     */
    public function it_should_hit_the_slack_api_when_receiving_a_gitlab_post(
        GuzzleClient $guzzleClient,
        GuzzleResponse $guzzleResponse,
        Mustache_Engine $mustacheEngine,
        Mustache_Loader_FilesystemLoader $fileSystemLoader
    ) {

        // make getenv() work with our fake slack url
        Dotenv::setEnvironmentVariable('SLACK_URL', $this->slackUrl);

        // mustache expectations
        $template = 'fake template';
        $fileSystemLoader->load('message')->shouldBeCalled()
            ->willReturn($template);
        $message = "test message";
        $mustacheEngine->render(
            $template,
            ["repositoryName" => $this->repositoryName]
        )->shouldBeCalled()->willReturn($message);

        // post to slack with all the expected stuff and get a response back.
        $postData = [
            'payload' => json_encode(
                ['text' => $message]
            )
        ];
        $guzzleClient->post($this->slackUrl, ['body' => $postData])
            ->shouldBeCalled()->willReturn($guzzleResponse);

        $this->indexAction()
            ->shouldHaveType('Symfony\Component\HttpFoundation\Response');
    }
}
