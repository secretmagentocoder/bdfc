<?php

namespace Ecommage\NavSyncPromotion\Model\Api;

use Laminas\Http\Client\Adapter\Curl as CurlClient;
use Laminas\Http\Response as HttpResponse;
use Laminas\Soap\Client as SOAPClient;
use Laminas\Soap\Client\Common as CommonClient;
use Laminas\Uri\Http as HttpUri;

class Nav  extends SOAPClient
{
    /**
     * Curl HTTP client adapter.
     * @var CurlClient
     */
    protected $curlClient = null;

    /**
     * The last request headers.
     * @var string
     */
    protected $lastRequestHeaders = '';

    /**
     * The last response headers.
     * @var string
     */
    protected $lastResponseHeaders = '';

    /**
     * SOAP client options.
     * @var array
     */
    protected $options = [];

    /**
     * Should NTLM authentication be used?
     * @var boolean
     */
    protected $useNtlm = false;

    /**
     * Constructor
     *
     * @param string $wsdl
     * @param array $options
     */
    public function __construct($wsdl = null, $options = null)
    {
        // Use SOAP 1.1 as default
        $this->setSoapVersion(SOAP_1_1);
        parent::__construct($wsdl, $options);
    }

    // @codingStandardsIgnoreStart
    /**
     * Do request proxy method.
     *
     * @param  CommonClient $client   Actual SOAP client.
     * @param  string       $request  The request body.
     * @param  string       $location The SOAP URI.
     * @param  string       $action   The SOAP action to call.
     * @param  int          $version  The SOAP version to use.
     * @param  int          $oneWay  (Optional) The number 1 if a response is not expected.
     * @return string The XML SOAP response.
     */
    public function _doRequest(CommonClient $client, $request, $location, $action, $version, $oneWay = null)
    {
        if (! $this->useNtlm) {
            return parent::_doRequest(
                $client,
                $request,
                $location,
                $action,
                $version,
                $oneWay
            );
        }

        $curlClient = $this->getCurlClient();

        // @todo persistent connection ?
        $headers    = [
            'Content-Type' => 'text/xml; charset=utf-8',
            'Method'       => 'POST',
            'SOAPAction'   => '"' . $action . '"',
            'User-Agent'   => 'PHP-SOAP-CURL',
        ];
        $uri = new HttpUri($location);

        // @todo use parent set* options for ssl certificate authorization
        $curlClient
            ->setCurlOption(CURLOPT_HTTPAUTH, CURLAUTH_NTLM)
            ->setCurlOption(CURLOPT_SSL_VERIFYHOST, false)
            ->setCurlOption(CURLOPT_SSL_VERIFYPEER, false)
            ->setCurlOption(CURLOPT_USERPWD, sprintf(
                '%s:%s',
                $this->options['login'],
                $this->options['password']
            ));

        // Perform the cURL request and get the response
        $curlClient->connect($uri->getHost(), $uri->getPort());
        $curlClient->write('POST', $uri, 1.1, $headers, $request);
        $response = HttpResponse::fromString($curlClient->read());
        $curlClient->close();

        // Save headers
        $this->lastRequestHeaders  = $this->flattenHeaders($headers);
        $this->lastResponseHeaders = $response->getHeaders()->toString();

        // Return only the XML body
        return $response->getBody();
    }
    // @codingStandardsIgnoreEnd

    /**
     * Returns the cURL client that is being used.
     *
     * @return CurlClient
     */
    public function getCurlClient()
    {
        if ($this->curlClient === null) {
            $this->curlClient = new CurlClient();
        }
        return $this->curlClient;
    }

    /**
     * Retrieve request headers.
     *
     * @return string Request headers.
     */
    public function getLastRequestHeaders()
    {
        return $this->lastRequestHeaders;
    }

    /**
     * Retrieve response headers (as string)
     *
     * @return string Response headers.
     */
    public function getLastResponseHeaders()
    {
        return $this->lastResponseHeaders;
    }

    /**
     * Sets the cURL client to use.
     *
     * @param  CurlClient $curlClient The cURL client.
     * @return self
     */
    public function setCurlClient(CurlClient $curlClient)
    {
        $this->curlClient = $curlClient;
        return $this;
    }

    /**
     * Sets options.
     *
     * Allows setting options as an associative array of option => value pairs.
     *
     * @param  array|\Traversable $options Options.
     * @throws \InvalidArgumentException If an unsupported option is passed.
     * @return self
     */
    public function setOptions($options)
    {
        if (isset($options['authentication']) && $options['authentication'] === 'ntlm') {
            $this->useNtlm = true;
            unset($options['authentication']);
        }

        $this->options = $options;
        return parent::setOptions($options);
    }

    // @codingStandardsIgnoreStart
    /**
     * Perform arguments pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param  array $arguments
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function _preProcessArguments($arguments)
    {
        if (count($arguments) > 1
            || (count($arguments) == 1  &&  ! is_array(reset($arguments)))
        ) {
            throw new Exception\RuntimeException(
                '.Net webservice arguments must be grouped into an array: array("a" => $a, "b" => $b, ...).'
            );
        }

        // Do nothing
        return $arguments;
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /**
     * Perform result pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param  object $result
     * @return mixed
     */
    protected function _preProcessResult($result)
    {
        $resultProperty = $this->getLastMethod() . 'Result';
        if (property_exists($result, $resultProperty)) {
            return $result->$resultProperty;
        }
        return $result;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Flattens an HTTP headers array into a string.
     *
     * @param  array $headers The headers to flatten.
     * @return string The headers string.
     */
    protected function flattenHeaders(array $headers)
    {
        $result = '';

        foreach ($headers as $name => $value) {
            $result .= $name . ': ' . $value . "\r\n";
        }

        return $result;
    }
}
