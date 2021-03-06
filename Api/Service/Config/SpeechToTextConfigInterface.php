<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Config;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
interface SpeechToTextConfigInterface
{
    /**
     * Get inpute selector
     *
     * @return string
     */
    public function getInputSelector();

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Check if example template is enabled
     *
     * @return boolean
     */
    public function isExampleTemplateEnabled();
}
