var Yozh = {
    options: {},
};

$(function () {
    
});

function call_user_func(functionName, context /*, args */) {
    var args = Array.prototype.slice.call(arguments, 2);
    var namespaces = functionName.split(".");
    var func = namespaces.pop();
    for (var i = 0; i < namespaces.length; i++) {
        context = context[namespaces[i]];
    }
    return context[func].apply(context, args);
}

function strtr(s, p, r) {

    //var s = this.toString();

    return !!s && {
        2: function () {
            for (var i in p) {
                s = strtr(s, i, p[i]);
            }
            return s;
        },
        3: function () {
            return s.replace(RegExp(p, 'g'), r);
        },
        0: function () {
            return;
        }
    }[arguments.length]();
}