// this file should normalize JS support
// across browsers
// and their implementation differences.


if (!window.CHelpers) window.CHelpers = {};

window.CHelpers.errorProcessor = function (formEl, errors) {
    Object.keys(errors).forEach(function(elId){
        var elErrors = errors[elId];
        var el = formEl.find("#"+elId+"");
        if( Object.prototype.toString.call( elErrors ) === '[object Array]' ) {
            // it s a list of error messages
            var containerEl = el.parent();
            containerEl.find('.errors').remove();
            var errorEl = $("<ul class='errors'></ul>");
            errorEl.appendTo(containerEl);
            $(elErrors).each(function (k, message) {
                $("<li>"+message+"</li>").appendTo(errorEl);
            })
        } else {
            // it s a list of sub fields, with error messages the function should continue down
        }
//                    form.find("[name='"+el+"']");
    })
};
window.CHelpers.formErrorProcessor = function (formEl) {
    return function (errors) {
        return window.CHelpers.errorProcessor(formEl, errors);
    };
};
