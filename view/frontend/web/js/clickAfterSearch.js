/* global ta */

define([
    'jquery',
], function($) {

    /**
     * Global settings
     *
     * @type {*}
     */
    var config = {};
    var sent = false;

    /**
     * Get element position
     *
     * @param {String} sku
     * @param {*} parent
     * @return {Number}
     */
    function getElementPosition(sku, parent) {
        var allSkus = $.unique($(parent).find(config.productLinksSelector).toArray().map((i) => $(i).attr(config.attributeName)));
        return allSkus.indexOf(sku);
    }

    function sentAnalytics(_this, _event) {
      require(['coveouascriptv2'], function(){
        window.initCoveo(function(){
        var element = $(_this);
        var linkValue = element.attr('href');
        var productSku = element.attr(config.attributeName);
        //For TESTING
        //var productSku = '24-MB01';
        var searchId = config.searchId;

        if (config.searchIdAttribute && element.get(0).hasAttribute(config.searchIdAttribute)) {
            searchId = element.attr(config.searchIdAttribute);
            console.debug('Coveo: using search id '+searchId+' from element attribute '+config.searchIdAttribute);
        }

        if (config.debug) {
            console.debug('Coveo: click after search captured');
        }

        if (coveoua === undefined) {
            console.warn('Coveo: Text Analytics is not included but analytics is active');
            return;
        }
        if (linkValue === null || linkValue === undefined) {
            console.warn('Coveo: click handled on a non-link element, href attribute not found');
            return;
        }
        if (productSku === null || productSku === undefined) {
            console.warn('Coveo: product link does not have the attribute '+config.attributeName);
            return;
        }

        var product = config.products[productSku];
        if (product === undefined) {
            if (config.paginationType && config.paginationType !== 'no-ajax') {
                if (config.paginationType === 'ajax-pagination') {
                    elaboratePaginationFromURL();
                }
                if (config.paginationType === 'ajax-infinite-scroll') {
                    config.pageSize = 0;
                    config.currentPage = 1;
                }
            }

            product = {
                id: productSku,
                brand: product['brand'],
                category: product['category'],
                name: product['name'],
                //position: product['position'],
                price: product['price'],
                quantity: product['quantity'],
                position: getElementPosition(productSku, _event.data.parent) + (config.pageSize * (config.currentPage - 1))
            }
        }
        coveoua('ec:addProduct', product);
        if (config.debug) {
            console.debug('Coveo: tracked product:', product);
        }
        coveoua('ec:setAction', 'click', {
            'list': 'coveo:search:'+searchId
        });

        var needRedirect = (_event.metaKey || _event.altKey || _event.ctrlKey) === false;

        var timeout = null;
        if (needRedirect) {
            setTimeout(function () {
                console.warn('Coveo: tracking system does not respond in time');
                document.location.href = linkValue; // fallback in case the library did not respond in time
            }, 1000);
        }

        coveoua('send', 'event', 'cart');
        document.location.href = linkValue;

        // We have to allow users to open links in a new window
        if (needRedirect === false) {
            return
        }
        return false;

      });
      });
    }
    /**
     * Click handler for product link elements
     *
     * @param {*} event
     */
    function clickHandler(event) {
      var _event = event;
      var _this = this;
      // Prevent default click behaviour

      event.preventDefault();
      event.stopPropagation();
      if (sent==true) return;
      sent = true;
      sentAnalytics(_this,_event);
      return false;
      
    }

    /**
     * Elaborate pagination from URL query parameters
     *
     */
    function elaboratePaginationFromURL() {
        var queries = {};
        $.each(document.location.search.substr(1).split('&'),function(c,q){
            var i = q.split('=');
            queries[i[0].toString()] = i[1].toString();
        });
        if (queries.product_list_limit !== undefined) {
            config.pageSize = parseInt(queries.product_list_limit);
            if (config.debug) {
                console.debug('Coveo: page size is now '+config.pageSize);
            }
        }
        if (queries.p !== undefined) {
            config.currentPage = parseInt(queries.p);
            if (config.debug) {
                console.debug('Coveo: current page is now '+config.currentPage);
            }
        }
    }

    /**
     * Configuration function
     *
     * @param {Object} configParams
     * @param {*} parentContainer
     */
    return function (configParams, parentContainer) {
        config = Object.assign({}, configParams);
        config.pageSize = config.pageSize || 0;
        config.currentPage = config.currentPage || 1;

        $(parentContainer).on(
            'click',
            config.productLinksSelector,
            {
                parent: parentContainer
            },
            clickHandler
        );
    }
});
