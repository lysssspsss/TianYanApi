window.onerror=function(){return true;}
var i = 0;
function blink() {
    document.getElementById("ftcolor").className = "changecolor" + i % 2;
    i++;
};
//setInterval(blink, 500);
/**
 * 创建保护罩
 * @return 返回元素对象，我们根据这个删除保护罩
 */
function createBg() {
    return $('<div class="screeningcan"></div>').appendTo('body').css({
        'width': $(document).width(),
        'height': $(document).height(),
        'position': 'absolute',
        'top': 0,
        'left': 0,
        'display': 'none',
        'z-index': 2,
        'opacity': 0.3,
        'filter': 'Alpha(Opacity = 30)',
        'backgroundColor': '#000'
    });
};
$(document).ready(function() {
    /**
     * 弹出登录框
     */
    (function() {
        var win_width = $(window).width();
        var win_height = $(window).height();
        var login = $('div.page-container');
        var screeningcan = createBg();
        $('.loginreg').click(function() {
            var width = win_width / 2 - (login.width() / 2) - 100;
            var height = win_height / 2 - (login.height() / 2);
            login.css({
                'top': height,
                'left': width,
                'z-index': 999
            });
            login.show();
            screeningcan.show();
        });
        screeningcan.click(function() {
            screeningcan.hide();
            login.hide();
        });
        /**
         * 添加验证方法
         * 以字母开头，5-17位 字母 数字 下划线
         */
        jQuery.validator.addMethod("user", function(value, element) {
            var tel = /^[\w]{6,16}$/;
            return this.optional(element) || (tel.test(value));
        }, "以字母，字母 数字 下划线,6-17位 ");
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
                code: {
                    required: true
                }
            },
            messages: {
                username: {
                    required: '账号不能为空'
                },
                password: {
                    required: '密码不能为空'
                },
                code: {
                    required: '验证码不能为空'
                }
            }
        });
    })();




    

    
});