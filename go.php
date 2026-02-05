<?php
// 查找WordPress安装目录
$wp_load_path = '';
$dir = dirname(__FILE__);
while ($dir != '/' && !file_exists($dir . '/wp-load.php')) {
    $dir = dirname($dir);
}
if (file_exists($dir . '/wp-load.php')) {
    $wp_load_path = $dir . '/wp-load.php';
} else {
    die('无法找到WordPress核心文件');
}

require_once($wp_load_path);

if (!isset($_GET['url']) || empty($_GET['url'])) {
    wp_redirect(home_url());
    exit;
}

try {
    $url = base64_decode($_GET['url']);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $options = get_option('lebase64url_options', array());
        
        if (!empty($options['use_redirect_page'])) {
            // 显示跳转中间页面
            ?>
            <!DOCTYPE html>
            <html <?php language_attributes(); ?>>
            <head>
                <meta charset="<?php bloginfo('charset'); ?>">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title><?php echo esc_html(get_bloginfo('name')); ?> - 跳转提示</title>
                <style>
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                        line-height: 1.6;
                        margin: 0;
                        padding: 20px;
                        background-color: #f5f5f5;
                    }
                    .container {
                        max-width: 600px;
                        margin: 50px auto;
                        padding: 30px;
                        background: white;
                        border-radius: 8px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    h1 {
                        color: #333;
                        margin-top: 0;
                    }
                    .url {
                        word-break: break-all;
                        background: #f8f8f8;
                        padding: 10px;
                        border-radius: 4px;
                        margin: 15px 0;
                    }
                    .button {
                        display: inline-block;
                        padding: 10px 20px;
                        background-color: #0073aa;
                        color: white;
                        text-decoration: none;
                        border-radius: 4px;
                        transition: background-color 0.3s;
                    }
                    .button:hover {
                        background-color: #005177;
                    }
                    .warning {
                        color: #856404;
                        background-color: #fff3cd;
                        border: 1px solid #ffeeba;
                        padding: 12px;
                        border-radius: 4px;
                        margin: 15px 0;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>外链跳转提示</h1>
                    <p>您即将离开 <?php echo esc_html(get_bloginfo('name')); ?> 访问以下网址：</p>
                    <div class="url"><?php echo esc_html($url); ?></div>
                    <div class="warning">
                        <strong>注意：</strong>该链接将带您离开本站。我们不能保证外部网站内容，注意您网络安全。
                    </div>
                    <p>如您确认要继续访问，请点击下面的按钮：</p>
                    <a href="<?php echo esc_url($url); ?>" class="button" rel="nofollow noopener noreferrer" target="_blank">继续访问</a>
                </div>
            </body>
            </html>
            <?php
        } else {
            // 直接跳转
            wp_redirect($url);
        }
    } else {
        wp_die('无效的URL格式');
    }
} catch (Exception $e) {
    wp_die('解析URL时发生错误');
}

exit;