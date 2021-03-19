<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service;

use Coveo\Search\SDK\Log\LoggerInterface as SDKLoggerInterface;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Wim Nijmeijer
 */
interface LoggerInterface extends SDKLoggerInterface
{
    /**
     * Logging facility
     *
     * @param string $message
     * @param int $level
     * @param array $context
     */
    public function log($message, $level = null, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function debug($message, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function warn($message, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function info($message, $context = []);
}
