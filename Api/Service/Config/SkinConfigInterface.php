<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Config;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface SkinConfigInterface
{
    /**
     * Get custom CSS
     *
     * @return string
     */
    public function getCustomCss();

    /**
     * Check is custom CSS is enabled
     *
     * @return boolean
     */
    public function isCustomCssEnabled();
}
