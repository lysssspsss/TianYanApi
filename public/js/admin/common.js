window.onerror=function(){return true;}
$(document).ready(function() {
    /**
     * 导航控制
     */
    (function() {
        var supnav = document.getElementById("supnav");
        var nav = document.getElementById("nav");
        var btns = document.getElementsByTagName("li");
        var subnavs = nav.getElementsByTagName("div");
        var paddingbottom = 20;
        var defaultHeight = 0;

        function drop(obj, ivalue) {
            var a = obj.offsetHeight;
            var speed = (ivalue - obj.offsetHeight) / 8;
            a += Math.floor(speed);
            obj.style.height = a + "px";
        }
        window.onload = function() {
            for (var i = 0; i < btns.length; i++) {
                btns[i].index = i;
                btns[i].onmouseover = function() {
                    var osubnav = subnavs[this.index];
                    var sublinks = osubnav.getElementsByTagName("a");
                    if (osubnav.firstChild.tagName == undefined) {
                        var itarheight = parseInt(osubnav.childNodes[1].offsetHeight) * sublinks.length + paddingbottom;
                    } else {
                        var itarheight = parseInt(osubnav.firstChild.offsetHeight) * sublinks.length + paddingbottom;
                    }
                    clearInterval(this.itimer);
                    this.itimer = setInterval(function() {
                        drop(osubnav, itarheight);
                    }, 30);
                }
                btns[i].onmouseout = function() {
                    var osubnav = subnavs[this.index];
                    clearInterval(this.itimer);
                    this.itimer = setInterval(function() {
                        drop(osubnav, defaultHeight);
                    }, 30);
                }
            }
        }
    })();
    /**
     * 生成用户
     */
    $('#makeUser').click(function() {
        if (confirm("请问是否立刻生成用户?")) {
            htmlobj = $.ajax({
                url: makeUserUrl,
                async: false
            });
            alert(htmlobj.responseText);
            //alert('hello \n world!');
        }
    });
    /**
     * 修改资料
     */
    $('[name=update]').click(function() {
        if (confirm("请问是否修改密码?")) {
            var pwd = prompt("请输入您的新密码");
            var id = $(this).attr('value');
            htmlobj = $.ajax({
                url: updateUrl,
                type: 'post',
                data: {
                    password: pwd,
                    uid: id
                },
                async: false
            });
            alert(htmlobj.responseText);
        }
    });
    /**
     * 删除用户
     */
    $('[name=delete]').click(function() {
        if (confirm("请问是否删除?")) {
            var id = $(this).attr('value');
            htmlobj = $.ajax({
                url: deleteUrl,
                type: 'post',
                data: {
                    uid: id
                },
                async: false
            });
            if(htmlobj.responseText == 1) {
                $(this).parent().html('<span style="color:red;">已删除</span>');
                alert('删除成功');
            } else {
                alert('删除失败');
            }
        }
    });

    /**
     * 期数添加框
     */
    (function() {
        var w, h, className;

        function getSrceenWH() {
            w = $(window).width();
            h = $(window).height();
            $('#dialogBg').width(w).height(h);
        }
        window.onresize = function() {
            getSrceenWH();
        }
        $(window).resize();
        $(function() {
            getSrceenWH();
            //显示弹框
            $('[name=addborder]').click(function() {
                className = $(this).attr('class');
                addalias = $(this).attr('alias');
                $('#dialogBg').fadeIn(300);
                $('#'+addalias).removeAttr('class').addClass('animated ' + className + '').fadeIn();
            });
            //关闭弹窗
            $('.claseDialogBtn').click(function() {
                $('#dialogBg').fadeOut(300, function() {
                    $('#'+addalias).addClass('bounceOutUp').fadeOut();
                });
            });
        });
    })();
});