define([
    'Magento_Customer/js/customer-data',
], function(customerData) {

    // Tracking operation timeout
    var timeout = null;

    // Prevent event send duplication
    var alreadySent = false;

    /**
     * Execute page tracking
     *
     * @param customerInfo
     */
    function executePageTracking(customerInfo) {
        var customerId = '';
        var storeId = '';
        if (customerInfo && customerInfo.customerId) {
            customerId = customerInfo.customerId;
        }
        if (customerInfo && customerInfo.storeId) {
          storeId = customerInfo.storeId;
      }
      if (alreadySent) {
            return;
        }
        initCoveo();
        coveoua('send', 'pageview');
        /* This sents a normal pageview and not a collect pageview */
        /*Parameters should be done by the coveoua script ????*/ 
        /*
        coveoua("send", "pageview", {
          contentIdKey: "@clickableuri",
          contentIdValue: window.location.href,
          contentType: "Product",
          context: '{"store_id":'+storeId+'}',
          context_UserId: customerId});*/

        if (timeout !== null) {
            clearTimeout(timeout);
        }
        alreadySent = true;
    }

    /**
     * Configuration function
     */
    return function () {
        var customer = customerData.get('customer');
        if (customerData.needReload() === true) {
            customer.subscribe(executePageTracking);
            timeout = setTimeout(function () {
                executePageTracking(customer());
            }, 1000);
            customerData.reload(['customer']);
        } else {
            executePageTracking(customer());
        }
    }

});
