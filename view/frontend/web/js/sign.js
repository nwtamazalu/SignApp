define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    'mage/url',
    'mage/translate'
], function($, _a, _u, _t){
    return function (config) {

        /**
         * Initiate signing process
         */
        $.ajax({
            'url': _u.build('sign/sign/sign'),
            'method': 'POST',
            'dataType': 'JSON',
            'data': {
                'initName': config.initName,
                'initEmail': config.initEmail,
                'initSsn': config.initSsn,
                'data': config.extra,
                'requestId': config.id,
                'request': config.request
            },
            'beforeSend': function() {
                //
            },
            'error': function(result) {
                __a('Error', result.message, {'always': function() { window.location.href = _u.build('sign/applications'); }});
            },
            'success': function(result) {
                if (result.success === 1) {
                    startCheckingOrderRef(result.orderRef, config);
                } else {
                    __a('Error', result.message, {'always': function() { window.location.href = _u.build('sign/applications'); }});
                }
            }
        });

        /**
         * Check orderRef
         * @param orderRef
         * @param config
         */
        function startCheckingOrderRef(orderRef, config)
        {
            let maxErrors = 10;
            let curErrors = 0;
            let interval = setInterval(function() {
                $.ajax({
                    'url': _u.build('sign/sign/check'),
                    'method': 'POST',
                    'dataType': 'JSON',
                    'data': {
                        'orderRef': orderRef,
                        'app_id': config.id
                    },
                    'success': function(result) {
                        console.log(result);
                        if (result.success === 0 || result.success === 1) {
                            clearInterval(interval);
                        }
                        if (result.success === 1) {
                            $.ajax({
                                'url': _u.build('sign/sign/save'),
                                'method': 'POST',
                                'dataType': 'JSON',
                                'data': {
                                    'completionData': result.details,
                                    'app_id': config.id
                                },
                                'success': function(result) {
                                    if (result.success === 1) {
                                        __a('Success', result.message, {'always': function() { window.location.href = _u.build('sign/applications'); }});
                                    } else {
                                        __a(
                                            'Error',
                                            _t('Could not save signature. Please try again. If this issue persists, please contact website administrators.')
                                            + ' :: ' + result.message,
                                            {'always': function() { window.location.href = _u.build('sign/applications'); }}
                                        );
                                    }
                                },
                                'error': function(result) {
                                    __a(
                                        'Error',
                                        _t('Could not save signature. Please try again. If this issue persists, please contact website administrators.')
                                        + ' :: ' + result.message,
                                        {'always': function() { window.location.href = _u.build('sign/applications'); }}
                                    );
                                }
                            });
                        } else if (result.success === 0) {
                            __a('Error', result.message, {'always': function() { window.location.href = _u.build('sign/applications'); }});
                        }
                    },
                    'error': function(result) {
                        if (curErrors < maxErrors) {
                            curErrors++;
                        } else {
                            __a('Error', result.message, {'always': function() { window.location.href = _u.build('sign/applications'); }});
                            clearInterval(interval);
                        }
                    }
                })
            }, 5000);
        }

        /**
         * Alert
         * @param title
         * @param content
         * @param actions
         * @param modalClass
         * @private
         */
        function __a(title, content, actions = {}, modalClass = 'confirm') { _a({ title: $.mage.__(title), content: $.mage.__(content), actions: actions, modalClass: modalClass }); }
    };
});
