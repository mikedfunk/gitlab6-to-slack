<?php
/**
 * Defines MikeFunk\Gitlab6ToSlack\Controllers\WebHookController
 *
 * @package MikeFunk\Gitlab6ToSlack\Controllers
 * @license MIT License <http://opensource.org/licenses/mit-license.html>
 */
namespace MikeFunk\Gitlab6ToSlack\Controllers;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;
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
    protected $httpRequest;

    /**
     * guzzlehttp client
     *
     * @var Client
     */
    protected $guzzleClient;

    /**
     * dependency injection
     *
     * @param Request $httpRequest
     * @param Client $guzzleClient
     * @return void
     */
    public function __construct(Request $httpRequest, Client $guzzleClient)
    {
        $this->httpRequest = $httpRequest;
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
        $payload = json_decode($this->httpRequest->request->get('payload'));
        $repositoryName = $payload->repository->name;
        $outgoingPostData = [
            'payload' => json_encode(
                ['text' => "new push to $repositoryName"]
            )
        ];
        $guzzleResponse = $this->guzzleClient
            ->post(getenv('SLACK_URL'), ['body' => $outgoingPostData]);
        return new SymfonyResponse();
    }
}
