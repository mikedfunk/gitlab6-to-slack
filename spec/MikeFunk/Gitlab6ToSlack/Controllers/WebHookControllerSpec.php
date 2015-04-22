<?php

/**
 * Specification unit test for MikeFunk\Gitlab6ToSlack\Controllers\WebHookController.
 *
 * @license MIT License <http://opensource.org/licenses/mit-license.html>
 */

namespace spec\MikeFunk\Gitlab6ToSlack\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyResponse;
use GuzzleHttp\Message\Response as GuzzleResponse;
use Dotenv;

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
    private $repositoryName = 'test repository';

    /**
     * slack url endpoint
     *
     * @var string
     */
    protected $slackUrl = 'test slack url';

    /**
     * let - test contructor.
     *
     * @test
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param GuzzleHttp\Client $guzzleClient
     */
    public function let(Request $request, GuzzleClient $guzzleClient)
    {
        Dotenv::setEnvironmentVariable('SLACK_URL', $this->slackUrl);
        $parameters = [
            'payload' => [
                'repository' => [
                    'name' => $this->repositoryName,
                ],
            ]
        ];
        $request->request = new ParameterBag($parameters);
        $this->beConstructedWith($request, $guzzleClient);
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
     * it_should_hit_the_slack_api_when_receiving_a_gitlab_post.
     *
     * @test
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param GuzzleHttp\Client $guzzleClient
     * @param  GuzzleHttp\Message\Response $guzzleResponse
     * @param Symfony\Component\HttpFoundation\Response $symfonyResponse
     */
    public function it_should_hit_the_slack_api_when_receiving_a_gitlab_post(
        Request $request,
        GuzzleClient $guzzleClient,
        GuzzleResponse $guzzleResponse,
        SymfonyResponse $symfonyResponse
    ) {
        $url = getenv('SLACK_URL');
        $postData = [
            'payload' => [
                'text' => "new push to $this->repositoryName",
            ],
        ];
        $guzzleClient->post($url, ['body' => $postData])->shouldBeCalled()
            ->willReturn($guzzleResponse);
        $this->indexAction()
            ->shouldHaveType('Symfony\Component\HttpFoundation\Response');
    }
}
