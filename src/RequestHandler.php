<?php

namespace HtmlDriven\CorsProxy;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\RequestException;

/**
 * Handles actual request sending.
 *
 * @author RebendaJiri <jiri.rebenda@htmldriven.com>
 */
class RequestHandler
{
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Sends the request to given URL and returns JSON response.
     *
     * @param string
     * @param string
     * @return void
     */
    public function handleRequest($method, $url)
    {
        $request = $this->client->createRequest($method, $url);

        $json = [
            'success' => true,
            'error' => null,
            'body' => null,
        ];

        $this->enableCrossDomainRequests();

        try {
            $result = $request->send(true);
        } catch (RequestException $e) {
            $json['success'] = false;
            $json['error'] = sprintf("Unable to handle request: CURL failed with message '%s'.", $e->getError());

            switch ($e->getErrorNo()) {
                case CURLE_COULDNT_RESOLVE_HOST:
                    http_response_code(404);
                    break;
                default:
                    http_response_code(400);
                    break;
            }
        }

        if (isset($result)) {
            $json['body'] = $result->getBody(true);
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Enables cross-domain requests.
     *
     * @return void
     */
    private function enableCrossDomainRequests()
    {
        header_remove('Access-Control-Allow-Origin');
        header('Access-Control-Allow-Origin: *');
    }
}
