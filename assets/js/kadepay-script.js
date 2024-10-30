jQuery(function($) {
    'use strict';
    var wc_kadepay_admin = {
        isTestMode: function() {
            return $('#woocommerce_kadepay_testmode').is(':checked')
        },
        init: function() {
            $(document.body).on('change', '#woocommerce_kadepay_testmode', function() {
                var test_merchant_id = $('#woocommerce_kadepay_sandbox_merchant_id').parents('tr').eq(0),
                    live_merchant_id = $('#woocommerce_kadepay_live_merchant_id').parents('tr').eq(0);
                if ($(this).is(':checked')) {
                    test_merchant_id.show();
                    live_merchant_id.hide();
                } else {
                    test_merchant_id.hide();
                    live_merchant_id.show();
                }
            });
            $('#woocommerce_kadepay_testmode').change()
        }
    };
    wc_kadepay_admin.init()
})
