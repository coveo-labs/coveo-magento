<?php
namespace Coveo\Search\SDK;

use \Exception as BaseException;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Exception extends BaseException
{
    /**
     * @var string
    */
    protected $_debugInfo = null;

    /**
     * @var Response
     */
    protected $_response = null;

    public function setDebugInfo($debugInfo)
    {
        $this->_debugInfo = $debugInfo;
    }

    public function getDebugInfo()
    {
        return $this->_debugInfo;
    }

    public function setResponse(Response $response)
    {
        $this->_response = $response;
    }

    public function getResponse()
    {
        return $this->_response;
    }
}