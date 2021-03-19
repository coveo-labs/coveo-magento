<?php
namespace Coveo\Search\SDK;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Response
{
    protected $_response = null;

    public function __construct($response = null)
    {
        if ($response) {
            $this->setResponse($response);
        }
    }

    public function setResponse($response)
    {
        $this->_response = $response;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function getObjectId()
    {
        return $this->_response !== null && isset($this->_response->searchUid) ? $this->_response->searchUid : null;
    }

    public function isValid()
    {
        return $this->_response !== null && isset($this->_response->results) && !isset($this->_response->exception);
    }

    public function getErrorCode()
    {
        return $this->_response->exception->code;
    }

    public function getErrorDescription()
    {
        return $this->_response->exception->context;
    }

    public function getErrorDebugInfo()
    {
        if(isset($this->_response->errors)){
            return $this->_response->errors;
        }else{
            return null;
        }
    }

}