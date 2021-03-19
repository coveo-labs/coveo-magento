<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Search;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface UrlRewriteSwitcherInterface
{
    /**
     * @param string $redirectUrl
     * @return string
     */
    public function elaborate($redirectUrl);
}
