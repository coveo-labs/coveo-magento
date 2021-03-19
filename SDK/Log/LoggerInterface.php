<?php
namespace Coveo\Search\SDK\Log;

use Coveo\Search\SDK\Exception;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
interface LoggerInterface
{
    /**
     * Logging facility
     *
     * @param string $message
     * @param int $level
     */
    public function log($message, $level = null);

    /**
     * @param Exception $e
    */
    public function logException(Exception $e);

    /**
     * @param string $message
     */
    public function debug($message);
}