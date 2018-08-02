<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CLTPHP管理系统安装--Powered by CLTPHP</title>
    <link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./css/install.css">
</head>
<body class="wp-core-ui">
    <p id="logo"><a href="https://www.cltphp.com/" tabindex="-1">CLTPHP</a></p>
    <h1 class="text-center">安装</h1>
    <article class="markdown-body entry-content" itemprop="text">
        <?php
            echo '<p>安装检测……</p>' . str_pad('', 4096) . "\n";
            ob_flush();
            flush();
            if ($error) {
                foreach ($error_message as $key => $value) { ?>
                    <li><?php echo $value; ?></li>
                <?php } ?>
                    <p class="step"><a href="###" onclick="javascript:history.go(-1);return false;" class="button button-large">重试</a></p>
            <?php } else {
                echo '<p>检测通过！</p>' . str_pad('', 4096) . "\n";
                echo '<p>初始化数据库……</p>' . str_pad('', 4096) . "\n";
                ob_flush();
                flush();

                $link = mysql_check($db_config);
                mysqli_query($link, "SET NAMES 'utf8'");

                if ($link != false) {
                    $sql = file_get_contents(dirname(__DIR__) . '/sql.sql');
                    _sql_execute($link, $sql, $db_config['DB_PREFIX']);
                    echo '<p>数据库设置完成！</p>';
                    echo '<p>创建管理员账号……</p>' . str_pad('', 4096) . "\n";
                    ob_flush();
                    flush();

                    $user = $_POST['user'];
                    $sql = "insert into `clt_admin` (`username`, `pwd`, `email`, `group_id`, `is_open`) values ('{$user['username']}', '" . md5($user['password']) . "', '', 1, 1);";
                    _sql_execute($link, $sql, $db_config['DB_PREFIX']);
                    echo '<p>创建管理员完成！</p>';
                    /*echo '<p>更新站点配置……</p>' . str_pad('', 4096) . "\n";
                    ob_flush();
                    flush();

                    $sql = "update `tp_site` set `base_domain` = '{$domain}', `domain` = 'http://www." . $domain . "';\n insert into `tp_options` (`key`, `value`) values ('base_domain', '{$domain}');";
                    _sql_execute($link, $sql, $db_config['DB_PREFIX']);
                    echo '<p>更新站点配置完成！</p>';*/

                    ?>
                    <p>安装完成</p>
                    <p class="step text-center"><a href="?action=grant" class="btn btn-primary btn-sm install-but">下一步</a></p>
                <?php
                }
            }
        ?>
    </article>
</body>
</html>