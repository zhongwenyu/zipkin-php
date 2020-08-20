<?php
namespace ybrenLib\zipkinphp\bean;

class ServiceZipkinBean{

    private $url;

    private $method;

    private $exception;

    private $startTimestamp;

    private $finishTimestamp;

    private $request;

    private $response;

    public function __construct(){
        $this->startTimestamp = \Zipkin\Timestamp\now();
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param mixed $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return int
     */
    public function getStartTimestamp()
    {
        return $this->startTimestamp;
    }

    /**
     * @param int $startTimestamp
     */
    public function setStartTimestamp($startTimestamp)
    {
        $this->startTimestamp = $startTimestamp;
    }

    /**
     * @return mixed
     */
    public function getFinishTimestamp()
    {
        return $this->finishTimestamp;
    }

    /**
     * @param mixed $finishTimestamp
     */
    public function setFinishTimestamp($finishTimestamp)
    {
        $this->finishTimestamp = $finishTimestamp;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}