const $ = jQuery;

$(document).ready(function() {
    const form = $('form.rn24SignupForm');
    const region = $('.sl2.regione');
    const group = $('.sl2.group');
    const email = form.find('#email');
    const submit = form.find('button[type="submit"]')

    region.select2();

    region.on('change', function() {
        $('.hideShowParent.group').show();
    });

    group.select2({
        width: 'resolve',
        ajax: {
            type: 'POST',
            url: rn24_ajax_object.ajaxurl,
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    region: region.val(),
                    action: rn24_ajax_object.action,
                };
            }
        }
    });

    group.on('change', function() {
        $('.hideShowParent.email').show();
    });


    group.on('select2:select', function (e) {
        var data = e.params.data;
        if(data.email) {
            email.val(data.email);
            submit.prop('disabled', false);
        }
    });

    email.on('change', function() {
        const check = email.val() ? false : true;
        submit.prop('disabled', check);
    });
});
