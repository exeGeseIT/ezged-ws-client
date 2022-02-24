<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Description of EzGEDResponseIAbstract
 *
 * @author Dev0ne <dev@docatwork.fr>
 */
abstract class EzGEDResponseAbstract implements EzGEDResponseInterface
{
    private ResponseInterface $httpresponse;
    protected ?ParameterBagInterface $content = null;
    
    protected int $error = 0;
    protected ?string $message = '';
    protected int $count = 0;
    
    
    abstract protected function initialize(array $payload): void;
    
    

    /**
     * Gets the HTTP headers of the http response.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @return string[][] The headers of the response keyed by header names in lowercase
     *
     * @throws TransportExceptionInterface   When a network error occurs
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
     * @throws ClientExceptionInterface      On a 4xx when $throw is true
     * @throws ServerExceptionInterface      On a 5xx when $throw is true
     */
    public function getHttpHeaders(bool $throw = true): array
    {
        return $this->httpresponse->getHeaders($throw);
    }

    /**
     * @throws TransportExceptionInterface when a network error occurs
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpresponse->getStatusCode();
    }
    
    /**
     * Returns info coming from the transport layer.
     *
     * This method SHOULD NOT throw any ExceptionInterface and SHOULD be non-blocking.
     * The returned info is "live": it can be empty and can change from one call to
     * another, as the request/response progresses.
     *
     * The following info MUST be returned:
     *  - canceled (bool) - true if the response was canceled using ResponseInterface::cancel(), false otherwise
     *  - error (string|null) - the error message when the transfer was aborted, null otherwise
     *  - http_code (int) - the last response code or 0 when it is not known yet
     *  - http_method (string) - the HTTP verb of the last request
     *  - redirect_count (int) - the number of redirects followed while executing the request
     *  - redirect_url (string|null) - the resolved location of redirect responses, null otherwise
     *  - response_headers (array) - an array modelled after the special $http_response_header variable
     *  - start_time (float) - the time when the request was sent or 0.0 when it's pending
     *  - url (string) - the last effective URL of the request
     *  - user_data (mixed) - the value of the "user_data" request option, null if not set
     *
     * When the "capture_peer_cert_chain" option is true, the "peer_certificate_chain"
     * attribute SHOULD list the peer certificates as an array of OpenSSL X.509 resources.
     *
     * Other info SHOULD be named after curl_getinfo()'s associative return value.
     *
     * @return mixed An array of all available info, or one of them when $type is
     *               provided, or null when an unsupported type is requested
     */
    public function getHttpInfo(string $type = null): array
    {
        return $this->httpresponse->getInfo($type);
    }
    
    
    
    public function isSucceed(): bool
    {
        return $this->error === 0;
    }
    
    public function getMessage(): ?string
    {
        return $this->message;
    }
    
    public function getCount(): int
    {
        return $this->count;
    }
    
    
    public function getContent(): ?ParameterBagInterface
    {
        return $this->content;
    }
    
    public function __construct(ResponseInterface $httpresponse)
    {
        $this->httpresponse = $httpresponse;
        
        try {
            /**
            * Gets the response body decoded as array, typically from a JSON payload.
            *
            * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
            *
            * @throws DecodingExceptionInterface    When the body cannot be decoded to an array
            * @throws TransportExceptionInterface   When a network error occurs
            * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
            * @throws ClientExceptionInterface      On a 4xx when $throw is true
            * @throws ServerExceptionInterface      On a 5xx when $throw is true
            */
            $payload = $httpresponse->toArray($throw=true);
            
            $this->count = isset($payload['count']) ? $payload['count'] : 0;
            $this->error = isset($payload['errorcode']) ? $payload['errorcode'] : 0;
            $this->message = isset($payload['errormsg']) ? $payload['errormsg'] : '';
            
            unset($payload['errorcode']);
            unset($payload['errormsg']);
            
            $this->initialize($payload);
            
        } catch (DecodingExceptionInterface $exc) {
            $this->error = 1;
            $this->message = $exc->getMessage();
        } catch (HttpExceptionInterface $httpExc) {
            $this->error = $this->getHttpStatusCode();
            $this->message = $httpExc->getMessage();
        }

    }
    
}
