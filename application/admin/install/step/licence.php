<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>CLTPHP管理系统安装--Powered by CLTPHP</title>
    <link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./css/install.css">
</head>
<body class="wp-core-ui">
<p id="logo"><a href="javascript:void(0);" tabindex="-1">CLTPHP</a></p>
<h2 class="text-center">最终用户授权许可协议</h2>
<article class="markdown-body entry-content" itemprop="text">
    <p>感谢您选择CLTPHP网站管理系统（以下简称CLTPHP），CLTPHP将为打造具有高效性的网站管理系统而不懈努力！</p>
    <p style="margin-bottom:10px;">本《CLTPHP企业网站管理系统最终用户授权许可协议》（以下简称“协议”）是您（自然人、法人或其他组织）与CLTPHP开发团队之间（以下简称“CLTPHP”）有关复制、下载、安装、使用CLTPHP的法律协议，同时本协议亦适用于任何有关CLTPHP的后期更新和升级。一旦复制、下载、安装或以其他方式使用CLTPHP，即表明您同意接受本协议各项条款的约束。<br/>
        <span style="color:#f00;">如果您不同意本协议中的条款，请勿复制、下载、安装或以其他方式使用CLTPHP。</span>
    </p>

    <h3>许可您的权利</h3>

    <ul class="license">
        <li>您可以将CLTPHP应用于非商业用途或个人网站，而不必支付软件版权授权费用。</li>
        <li>您可以根据需要对CLTPHP进行必要的修改和美化，以适应您的网站要求。</li>
        <li>您拥有使用CLTPHP构建的网站中的全部内容的所有权，并独立承担与内容相关的法律义务。</li>
    </ul>

    <h3>有限担保和免责声明</h3>
    <ul class="license">
        <li>CLTPHP及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。</li>
        <li>用户出于自愿而使用CLTPHP，<span style="color:#f00;">您必须了解使用CLTPHP的风险，我们不承诺提供任何形式的技术支持、使用担保，也不承担任何因使用CLTPHP而产生问题的相关责任。</span>
        </li>
        <li>CLTPHP不对使用CLTPHP构建的网站的任何信息内容以及导致的任何版权纠纷和法律争议及后果承担责任。</li>
    </ul>
</article>
<div class="checkbox">
    <label>
        <input type="checkbox" name="agree" id="agree_licence"> 我已阅读并同意该协议
    </label>
    <a href="./index.php?action=requirement" class="btn btn-primary btn-sm install-but">下一步</a>
</div>
<script type="text/javascript" src="./js/jquery.min.js"></script>
<script type="text/javascript">
    $('.install-but').click(function() {
        if (!$('#agree_licence').prop('checked')) {
            alert('你必须同意用户协议！');
            return false;
        }
    });
</script>
</body>
</html>
