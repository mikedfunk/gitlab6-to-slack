<?php
/**
 * Defines MikeFunk\Gitlab6ToSlack\Controllers\WebHookController
 *
 * @package MikeFunk\Gitlab6ToSlack\Controllers
 * @license MIT License <http://opensource.org/licenses/mit-license.html>
 */
namespace MikeFunk\Gitlab6ToSlack\Controllers;

use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * WebHookController
 *
 * @author Michael Funk <mike@mikefunk.com>
 * @see spec\MikeFunk\Gitlab6ToSlack\Controllers\WebHookControllerSpec
 */
class WebHookController
{

    /**
     * symfony request object
     *
     * @var Request
     */
    protected $request;

    /**
     * guzzlehttp client
     *
     * @var Client
     */
    protected $guzzleClient;

    /**
     * dependency injection
     *
     * @param Request $request
     * @param Client $guzzleClient
     * @return void
     */
    public function __construct(Request $request, Client $guzzleClient)
    {
        $this->request = $request;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * indexAction
     *
     * @return Response
     */
    public function indexAction()
    {
        // send the request to the slack api and get a response
        // TODO get repository name
        $payload = $this->request->request->get('payload');
        $repositoryName = $payload['repository']['name'];
        $outgoingPostData = [
            'payload' => [
                'text' => "new push to $repositoryName",
            ],
        ];
        $guzzleResponse = $this->guzzleClient
            ->post(getenv('SLACK_URL'), ['body' => $outgoingPostData]);
        return new SymfonyResponse();
    }
}
