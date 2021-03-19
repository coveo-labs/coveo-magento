<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Config;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface SuggestionConfigInterface
{
    /**
     * Get suggestion library endpoint
     *
     * @return string
     */
    public function getLibraryEndpoint();

    /**
     * Get suggestion input selector
     *
     * @return mixed
     */
    public function getInputSelector();

    /**
     * Get suggestion init parameters
     *
     * @return mixed
     */
    public function getInitParams();

    /**
     * Get on select value
     *
     * @return mixed
     */
    public function getOnSelectCallbackValue();

    /**
     * Get width value
     *
     * @return mixed
     */
    public function getWidthValue();

    /**
     * Check if suggestion library is included
     *
     * @return mixed
     */
    public function isLibraryIncluded();
}
