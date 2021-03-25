# Coveo Search for Magento 2

[Coveo](https://www.coveo.com) is a cloud-based, multi-language search tool for e-commerce.

This extension replaces the default search of Magento with a typo-tolerant, fast & relevant search experience backed by [Coveo](https://www.coveo.com).

## Description

This extension replaces the default Magento search engine with one based on Coveo API.
It provide the following features:

* Fulltext search for catalog products (currently advanced search is not supported)
* Scheduled indexing of catalog products (under development)
* Automatic typo correction (under development)
* ML Search keywords suggest

## Requirements

* PHP > 7.0
* [Composer](https://getcomposer.org/)
* Magento >= 2.3

## Installation Instructions

### Latest version

Install latest version using composer:
```bash
composer require coveo/magento-2-search
```

### Specific version

Install a specific version using composer:
```bash
composer require coveo/magento-2-search:1.0.0
```

### Development version

Set "minimum-stability" to "dev" and switch off the "prefer-stable" config:
```bash
composer config minimum-stability dev
composer config prefer-stable false
```

Install latest development version using composer:
```bash
composer require coveo/magento-2-search:dev-develop
```

## Module Configuration

### Request your Coveo Cloud Organization
Goto: [Coveo](https://www.coveo.com) 

### Create your Push source
Goto: [Push](https://docs.coveo.com/en/68)
Copy the created Push API Key in your configuration.

### Create your Search API key
Goto: [Search Api](https://docs.coveo.com/en/82).
Copy the created Push API Key in your configuration.

### Create your Fields (in the Coveo Platform)
Goto: [Create Fields](https://docs.coveo.com/en/1982).

Create the following fields:

| Field name | Type | Settings |
|---|----|---|
|sku| String | |
|store_id| String | |
|price | Decimal | |


### Set your configuration: 
1. Under __API Configuration__
* Insert 

* __Send report__: __YES__ to send a report to Coveo when an API error occourred	
* __Debug mode__:  __Yes__ to enable more verbose logging for debug purpose
2. Save configuration


## Programmatically use Coveo service

If you would like to call Coveo service that currently are not supported with the plugin we suggest this configuration.

Include in your class a dependency from `Coveo\Search\Api\Service\ClientInterface` and let DI system do the rest:
```php
<?php

use Coveo\SDK\ClientBuilder;
use Coveo\Search\Api\Service\ConfigInterface;
use Coveo\Search\Api\Service\TrackingInterface;
use Coveo\Search\Api\Service\LoggerInterface;

class MyServiceClass
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Search constructor.
     *
     * @param ConfigInterface $config
     * @param TrackingInterface $tracking
     * @param ClientBuilder $clientBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        TrackingInterface $tracking,
        ClientBuilder $clientBuilder,
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->tracking = $tracking;
        $this->clientBuilder = $clientBuilder;
        $this->logger = $logger;
    }

}

```
build the client instance using a `Coveo\SDK\ClientBuilder` instance
```php
<?php
    
    /**
     * Get Client
     *
     * @return \Coveo\SDK\Client
     */
    protected function getClient()
    {
        return $this->clientBuilder
            ->withApiKey($this->config->getApiKeySearch())
            ->withApiBaseUrl($this->config->getApiSearchUrl())
            ->withSessionStorage($this->tracking->getSession())
            ->withLanguage($this->config->getLanguage())
            ->withStoreCode($this->config->getStoreId())
            ->withAgent($this->tracking->getApiAgent())
            ->withLogger($this->logger)
            ->withPipeline('Recommendations') //optional Query Pipeline to use
            ->withRecommendations(true)
            ->build();
    }
``` 
The `withRecommendations` will enable the proper analytics events for a recommendation call.
The `withPipeline` you can target a specific pipeline to trigger the results on.


this allow you to create a `Coveo\SDK\Client` instance to made any HTTP call to Coveo API with the pre-configured required parameters:
```php
<?php

    /**
     * Execute service
     */
    protected function execute()
    {
        $client = $this->getClient();
        $result = $client->doRequest('/search', \Coveo\SDK\Client::HTTP_METHOD_GET, [
            'param1' => 'value1',
            'param2' => 'value2'
        ]);
    }
```
access response data from the object of type `Coveo\SDK\Response` returned by `doRequest` method:
```php
<?php
$result = $client->doRequest('/search', \Coveo\SDK\Client::HTTP_METHOD_GET, [
    'param1' => 'value1',
    'param2' => 'value2'
]);
$responseData = $result->getResponse();
```

## Required theme changes

### Product click after search tracking

In order to make product click after search tracking work the frontend script need to find 
an attribute `data-product-sku` on the `a` tag with the product's link valorized with the product SKU. 
To do this edit your theme file `view/frontend/templates/product/list.phtml`, inside the product list cycle, in the following way:
```php
<a href="<?= /* @escapeNotVerified */ $_product->getProductUrl() ?>" data-product-sku="<?= $block->escapeHtml($_product->getSku()) ?>" class="product photo product-item-photo" tabindex="-1">
    <?= $productImage->toHtml() ?>
</a>
<div class="product details product-item-details">
  <!-- here the product details -->
```

If you are using an AJAX pagination approach (for example an infinite scrolling loading) you also have to add an attribute `data-search-id` on the `a` tag with the product's link.
In order to to this edit your theme file `view/frontend/templates/product/list.phtml` to load the Coveo Search ID value from registry key `coveo_search_response` (available from `\Coveo\Search\Model\Service\Search::SEARCH_RESULT_REGISTRY_KEY` class constant), 
in this way:
```php
<?php
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
/** @var \Coveo\SDK\Search\Result $searchResult */
$searchResult = $objectManager->get('Magento\Framework\Registry')->registry(\Coveo\Search\Model\Service\Search::SEARCH_RESULT_REGISTRY_KEY);
?>

<!-- your product collection loop -->

<a href="<?= /* @escapeNotVerified */ $_product->getProductUrl() ?>" data-product-sku="<?= $block->escapeHtml($_product->getSku()) ?>" data-search-id="<?= $searchResult->getSearchId() ?>" class="product photo product-item-photo" tabindex="-1">
    <?= $productImage->toHtml() ?>
</a>
<div class="product details product-item-details">
  <!-- here the product details -->
```
Obviously, this is just an example, is better to inject `Magento\Framework\Registry` dependency in your product's list controller using the [DI approach](https://devdocs.magento.com/guides/v2.2/extension-dev-guide/depend-inj.html).

### Suggestion frontend

This module can provide a suggestion on the search input, when you enable this feature you have to remove the default Magento suggestions system.
In order to do this remove the Magento suggestion initialization from your template or override the default template `view/frontend/templates/form.mini.phtml`.
Remove this initialization attribute from the search input:
```html
data-mage-init='{"quickSearch":{
    "formSelector":"#search_mini_form",
    "url":"<?= /* @escapeNotVerified */ $helper->getSuggestUrl()?>",
    "destinationSelector":"#search_autocomplete"}
}'
```  
and remove the suggestions container from the template:
```html
<div id="search_autocomplete" class="search-autocomplete"></div>
```
