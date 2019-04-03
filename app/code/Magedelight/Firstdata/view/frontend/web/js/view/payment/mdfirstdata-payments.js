/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'md_firstdata',
                component: 'Magedelight_Firstdata/js/view/payment/method-renderer/firstdata-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);