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
use Dotenv;
use UnexpectedValueException;
use RuntimeException;
use ErrorException;

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
     * @throws RuntimeException if SLACK_URL env var is not defined
     * @throws UnexpectedValueException if payload is not valid json
     * @throws ErrorException if POST does not have the right structure
     * @return Response
     */
    public function indexAction()
    {
        Dotenv::required('SLACK_URL');

        // send the request to the slack api and get a response
        $payload = json_decode($this->httpRequest->request->get('payload'));

        // it must be valid json
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException('Payload was not valid json');
        }
        // POST must have the right structure
        if (!isset($payload->repository) || !isset($payload->repository->name)) {
            throw new ErrorException(
                'Incoming POST does not have payload->repository->name'
            );
        }
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
