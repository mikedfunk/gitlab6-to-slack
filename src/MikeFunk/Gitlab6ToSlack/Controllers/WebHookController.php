<?php
/**
 * Defines MikeFunk\Gitlab6ToSlack\Controllers\WebHookController
 *
 * @package MikeFunk\Gitlab6ToSlack\Controllers
 * @license MIT License <http://opensource.org/licenses/mit-license.html>
 */
namespace MikeFunk\Gitlab6ToSlack\Controllers;

use Dotenv;
use ErrorException;
use GuzzleHttp\Client;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use UnexpectedValueException;

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
     * mustache engine instance
     *
     * @var Mustache_Engine
     */
    protected $mustacheEngine;

    /**
     * file system loader
     *
     * @var Mustache_Loader_FilesystemLoader
     */
    protected $fileSystemLoader;

    /**
     * dependency injection
     *
     * @param Request $httpRequest
     * @param Client $guzzleClient
     * @param Mustache_Engine $mustacheEngine
     * @param Mustache_Loader_FilesystemLoader $fileSystemLoader
     * @return void
     */
    public function __construct(
        Request $httpRequest,
        Client $guzzleClient,
        Mustache_Engine $mustacheEngine,
        Mustache_Loader_FilesystemLoader $fileSystemLoader
    ) {
        $this->httpRequest = $httpRequest;
        $this->guzzleClient = $guzzleClient;
        $this->mustacheEngine = $mustacheEngine;
        $this->fileSystemLoader = $fileSystemLoader;
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

        // set up the message view
        $repositoryName = $payload->repository->name;
        $template = $this->fileSystemLoader->load('message');
        $message = $this->mustacheEngine
            ->render($template, ['repositoryName' => $repositoryName]);

        // set up post data for slack
        $outgoingPostData = ['payload' => json_encode(['text' => $message])];

        // describe any guzzle exceptions
        try {
            $guzzleResponse = $this->guzzleClient
                ->post(getenv('SLACK_URL'), ['body' => $outgoingPostData]);
        } catch (TransferException $e) {
             $response = "There was an error sending data to the Slack API:\n";
             $response += $e->getMessage();
             return new SymfonyResponse($response);
        }
        return new SymfonyResponse();
    }
}
