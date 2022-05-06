<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

use ExeGeseIT\EzGEDWsClient\Core\Dto\EzBagInterface;
use ExeGeseIT\EzGEDWsClient\Core\ParameterBag\ParameterBagInterface;
use ExeGeseIT\EzGEDWsClient\Exception\HttpClientException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Description of EzGEDResponseIAbstract
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
abstract class EzGEDResponseAbstract implements EzGEDResponseInterface
{
    private ResponseInterface $httpresponse;
    protected ?ParameterBagInterface $content = null;
    
    protected int $error = 0;
    protected ?string $message = '';
    protected int $count = 0;
    
    
    abstract protected function initialize(array $payload): void;
    
    

    protected function tokenize(EzBagInterface $ezBag): string
    {
        return $ezBag->getId() ?? sprintf('_%s', bin2hex(random_bytes(16)));
    }
    
    /**
     * {@inheritDoc}
     */
    public function getHttpHeaders(bool $throw = true): array
    {
        return $this->httpresponse->getHeaders($throw);
    }

    /**
     * {@inheritDoc}
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpresponse->getStatusCode();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getHttpInfo(string $type = null)
    {
        return $this->httpresponse->getInfo($type);
    }
    
    
    /**
     * {@inheritDoc}
     */
    public function getErrorNumber(): int
    {
        return $this->error;
    }
    
    /**
     * {@inheritDoc}
     */
    public function isSucceed(): bool
    {
        return $this->error === 0;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getCount(): int
    {
        return $this->count;
    }
    
    /**
     * {@inheritDoc}
     */
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
            
            $this->count = isset($payload['count']) ? (int)$payload['count'] : 0;
            $this->error = isset($payload['errorcode']) ? $payload['errorcode'] : 0;
            $this->message = isset($payload['errormsg']) ? $payload['errormsg'] : '';
            
            unset($payload['count']);
            unset($payload['errorcode']);
            unset($payload['errormsg']);
            
            $this->initialize($payload);
            
        } catch (\Exception $exc) {
            $this->error = $this->getHttpStatusCode();
            $this->message = $exc->getMessage();
            throw new HttpClientException($exc);
        }
    }
}
