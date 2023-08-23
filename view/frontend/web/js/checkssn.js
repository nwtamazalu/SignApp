// noinspection DuplicatedCode

define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    'mage/url',
    'mage/translate'
], function($, _a, _u, _t){
    return function (config) {
        let typingTimer;
        const doneTypingInterval = 1000;
        const $input = $('.signapp-ssn input, .field-signapp-ssn input');
        $input.on('keyup change input', function (e) {
            if ((/[a-zA-Z0-9-_ ]/.test(e.keyCode)) === false || e.ctrlKey || e.shiftKey || e.altKey || e.metaKey) return;
            clearTimeout(typingTimer);
            typingTimer = setTimeout(doneTyping, doneTypingInterval);
        });
        $input.on('keydown', function (e) {
            if ((/[a-zA-Z0-9-_ ]/.test(e.keyCode)) === false || e.ctrlKey || e.shiftKey || e.altKey || e.metaKey) return;
            clearTimeout(typingTimer);
        });
        function doneTyping () {
            if (testSsn($input.val()) === true) {
                $.ajax({
                    'url': _u.build('sign/checkssn/index'),
                    'method': 'POST',
                    'dataType': 'JSON',
                    'data': {
                        'ssn': $input.val()
                    },
                    'success': function(result) {
                        $('.signapp-email, .field-signapp-email').removeClass('hidden').show();
                        $('.signapp-email input, .field-signapp-email input').val(result.email);
                    },
                    'error': function(result) {
                        __a('Error', 'Something went wrong');
                    }
                });
            } else {
                $('.signapp-email, .field-signapp-email').addClass('hidden').hide();
                $('.signapp-email input, .field-signapp-email input').val('');
            }
        }

        /**
         * Check ssn
         * @param input
         * @returns {string|boolean}
         */
        function testSsn(input) {
            let val = input.replace(/[^0-9]/g, "").toString();
            if ( val.length > 10 ) {
                val = val.substring(val.length, val.length-10);
            }
            if( !val.match(/[0-9]{10}/) ) {
                return "Bad length"
            }
            if( !val.match(/([0-9][0-9]).{8}/) ) {
                return "Bad year in date: " + val
            }
            if( !val.match(/.{2}(0[1-9]|1[0-2]).{6}/) ) {
                return "Bad month in date"
            }
            if( !val.match(/.{4}(0[1-9]|[1-2][0-9]|3[0-1]).{4}/) ) {
                return "Bad day in date"
            }
            let valArr = val.split(""),
                luhn = 0;
            luhn =  (valArr[0] * 2).toString() +
                (valArr[1] * 1).toString() +
                (valArr[2] * 2).toString() +
                (valArr[3] * 1).toString() +
                (valArr[4] * 2).toString() +
                (valArr[5] * 1).toString() +
                (valArr[6] * 2).toString() +
                (valArr[7] * 1).toString() +
                (valArr[8] * 2).toString()
            let luhnArr = luhn.split("");
            let result = 0;
            $.each(luhnArr,function() {
                result += parseInt(this);
            });
            result = 10 - (result % 10);
            let controlNr = parseInt(val.substring(9,10));
            return controlNr === result;
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
