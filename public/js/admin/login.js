$().ready(function() {
        /**
         * 添加验证方法
         * 以字母开头，5-17位 字母 数字 下划线
         */
        jQuery.validator.addMethod("user", function(value, element) {
            var tel = /^[\w]{6,16}$/;
            return this.optional(element) || (tel.test(value));
        }, "");

        $('form[name=loginbox]').validate({
            success: function(label) {
                label.addClass('success');
            },
            errorElement: 'div',
            rules: {
                username: {
                    required: true,
                    user: true
                },
                password: {
                    required: true,
                    user: true
                },
                verify: {
                    required: true,
                    maxlength: 4,
                    minlength: 4
                }
            },
            messages: {
                username: {
                    required: '',

                },
                password: {
                    required: ' '
                },
                verify: {
                    required: ' '
                }
            }
        });

});