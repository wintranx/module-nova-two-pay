define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_NovaTwoPay/payment/form'
            },
            // add required logic here
        });
    }
);
