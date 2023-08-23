// noinspection DuplicatedCode

define([
    "jquery"
], function($){
    return function () {
        $('.apply-form input').on('change', function(e) {
            let field = $(this).closest('.field').data('dependency');
            // console.log('Field: ' + field);
            let value = $(document).find('[name="' + field + '"]:checked').val();
            // console.log('Value: ' + value);
            let dependencies = $('[dependency-field="' + field + '"]');
            // console.log('Dependencies:');
            // console.log(dependencies);

            $(dependencies).each(function(k,v) {
                $(v).closest('.field').css('display', 'none');
                // console.log($(v).attr('dependency-value') + ' , ' + value);
               if ($(v).attr('dependency-value') === value) {
                   $(v).closest('.field').css('display', 'block');
               }
            });
        });
    };
});
