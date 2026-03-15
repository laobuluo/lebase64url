<?php
/**
 * Plugin Name: LeBase64URL
 * Plugin URI:  https://www.laojiang.me/6113.html
 * Description: 一个适用于WordPress的外链接跳转将文章中的外部链接转换为base64加密的格式，支持nofollow属性和白名单设置。公众号：<span style="color: red;">老蒋朋友圈</span>
 * Version: 1.0.0
 * Author: 老蒋和他的小伙伴
 * Author URI: https://www.laojiang.me
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lebase64url
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeBase64URL {
    private static $instance = null;
    private $options;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->options = get_option('lebase64url_options', array(
            'enabled' => false,
            'prefix' => 'go.php?url=',
            'whitelist' => '',
            'use_redirect_page' => true,
            'add_nofollow' => false,
            'add_blank' => false
        ));

        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_filter('the_content', array($this, 'process_content'));
        add_action('init', array($this, 'handle_redirect'));
        
        // 添加重写规则
        add_action('init', array($this, 'add_rewrite_rules'));
        
        // 添加查询变量
        add_filter('query_vars', array($this, 'add_query_vars'));
    }

    public function activate() {
        // 激活插件时的操作
        if (!get_option('lebase64url_options')) {
            add_option('lebase64url_options', array(
                'enabled' => false,
                'prefix' => 'go.php?url=',
                'whitelist' => '',
                'use_redirect_page' => true,
                'add_nofollow' => false,
                'add_blank' => false
            ));
        }
        
        // 刷新重写规则
        flush_rewrite_rules();
        update_option('lebase64url_rewrite_rules_flushed', '1');
    }

    public function deactivate() {
        // 停用插件时的操作
        delete_option('lebase64url_rewrite_rules_flushed');
        flush_rewrite_rules();
    }

    public function uninstall() {
        // 删除插件时的操作
        delete_option('lebase64url_options');
    }

    public function add_plugin_page() {
        add_options_page(
            '外链Base64加密设置', 
            'LeBase64URL设置',
            'manage_options',
            'lebase64url-settings',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>LeBase64URL 设置</h1>
            <form method="post" action="options.php">
            <?php
                settings_fields('lebase64url_options_group');
                do_settings_sections('lebase64url-settings');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'lebase64url_options_group',
            'lebase64url_options',
            array($this, 'sanitize')
        );

        add_settings_section(
            'lebase64url_setting_section',
            '插件设置',
            array($this, 'section_info'),
            'lebase64url-settings'
        );

        add_settings_field(
            'enabled',
            '启用插件',
            array($this, 'enabled_callback'),
            'lebase64url-settings',
            'lebase64url_setting_section'
        );

        add_settings_field(
            'prefix',
            'URL前缀',
            array($this, 'prefix_callback'),
            'lebase64url-settings',
            'lebase64url_setting_section'
        );

        add_settings_field(
            'whitelist',
            '白名单域名',
            array($this, 'whitelist_callback'),
            'lebase64url-settings',
            'lebase64url_setting_section'
        );

        add_settings_field(
            'use_redirect_page',
            '使用中间页面',
            array($this, 'use_redirect_page_callback'),
            'lebase64url-settings',
            'lebase64url_setting_section'
        );

        add_settings_field(
            'add_nofollow',
            '添加nofollow属性',
            array($this, 'add_nofollow_callback'),
            'lebase64url-settings',
            'lebase64url_setting_section'
        );

        add_settings_field(
            'add_blank',
            '新窗口打开',
            array($this, 'add_blank_callback'),
            'lebase64url-settings',
            'lebase64url_setting_section'
        );
    }

    public function process_content($content) {
        if (!$this->options['enabled']) {
            return $content;
        }

        $site_url = get_site_url();
        $whitelist = array_filter(explode('\n', $this->options['whitelist']));
        $plugin_url = plugin_dir_url(__FILE__);

        // 使用正则表达式匹配所有链接
        $pattern = '/<a([^>]*?)href=[\'"]([^\'"]*)[\'"](.*?)>(.*?)<\/a>/i';
        return preg_replace_callback($pattern, function($matches) use ($site_url, $whitelist, $plugin_url) {
            $attrs = $matches[1];
            $url = $matches[2];
            $other_attrs = $matches[3];
            $text = $matches[4];

            // 检查是否为外部链接
            if (strpos($url, $site_url) === 0 || strpos($url, '/') === 0) {
                return $matches[0]; // 内部链接，保持不变
            }

            // 检查是否在白名单中
            $domain = parse_url($url, PHP_URL_HOST);
            foreach ($whitelist as $white_domain) {
                if (stripos($domain, trim($white_domain)) !== false) {
                    return $matches[0]; // 白名单域名，保持不变
                }
            }

            // 添加 nofollow 属性
            if ($this->options['add_nofollow']) {
                if (strpos($other_attrs, 'rel=') === false) {
                    $other_attrs .= ' rel="nofollow"';
                } elseif (strpos($other_attrs, 'nofollow') === false) {
                    $other_attrs = preg_replace('/rel=(["\'])(.*?)(["\'])/', 'rel=$1$2 nofollow$3', $other_attrs);
                }
            }

            // 添加新窗口打开属性
            if ($this->options['add_blank']) {
                if (strpos($other_attrs, 'target=') === false) {
                    $other_attrs .= ' target="_blank"';
                }
                // 添加安全属性
                if (strpos($other_attrs, 'rel=') === false) {
                    $other_attrs .= ' rel="noopener noreferrer"';
                } elseif (strpos($other_attrs, 'noopener') === false || strpos($other_attrs, 'noreferrer') === false) {
                    $other_attrs = preg_replace('/rel=(["\'])(.*?)(["\'])/', 'rel=$1$2 noopener noreferrer$3', $other_attrs);
                }
            }

            // 生成加密URL
            $encoded_url = $plugin_url . $this->options['prefix'] . base64_encode($url);

            return sprintf('<a%shref="%s"%s>%s</a>',
                $attrs,
                esc_url($encoded_url),
                $other_attrs,
                $text
            );
        }, $content);
    }

    public function handle_redirect() {
        $redirect_param = get_query_var('lebase64url_redirect');
        
        if (!empty($redirect_param)) {
            try {
                $url = base64_decode($redirect_param);
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    if ($this->options['use_redirect_page']) {
                        // 显示中间页面
                        include(plugin_dir_path(__FILE__) . 'templates/redirect.php');
                        exit;
                    } else {
                        // 直接跳转
                        wp_redirect($url);
                        exit;
                    }
                } else {
                    wp_die('无效的URL格式');
                }
            } catch (Exception $e) {
                wp_die('解析URL时发生错误');
            }
        }
    }

    public function sanitize($input) {
        $new_input = array();

        $new_input['enabled'] = isset($input['enabled']);
        $new_input['prefix'] = sanitize_text_field($input['prefix']);
        $new_input['whitelist'] = sanitize_textarea_field($input['whitelist']);
        $new_input['use_redirect_page'] = isset($input['use_redirect_page']);
        $new_input['add_nofollow'] = isset($input['add_nofollow']);
        $new_input['add_blank'] = isset($input['add_blank']);

        return $new_input;
    }

    public function section_info() {
        echo '在这里，我们需要配置LeBase64URL插件的相关选项。<a href="hhttps://www.laojiang.me/6113.html" target="_blank">插件介绍</a>（关注公众号：<span style="color: red;">老蒋朋友圈</span>）';
    }

    public function enabled_callback() {
        printf(
            '<input type="checkbox" name="lebase64url_options[enabled]" %s />',
            (isset($this->options['enabled']) && $this->options['enabled']) ? 'checked' : ''
        );
    }

    public function prefix_callback() {
        printf(
            '<input type="text" id="prefix" name="lebase64url_options[prefix]" value="%s" class="regular-text" />'
            . '<p class="description">设置加密URL的前缀，默认为：go.php?url=</p>',
            isset($this->options['prefix']) ? esc_attr($this->options['prefix']) : ''
        );
    }

    public function whitelist_callback() {
        printf(
            '<textarea id="whitelist" name="lebase64url_options[whitelist]" rows="5" cols="50" class="large-text">%s</textarea>'
            . '<p class="description">每行输入一个域名，这些域名的链接将不会被加密。例如：laojiang.me</p>',
            isset($this->options['whitelist']) ? esc_textarea($this->options['whitelist']) : ''
        );
    }

    public function use_redirect_page_callback() {
        printf(
            '<input type="checkbox" name="lebase64url_options[use_redirect_page]" %s />'
            . '<p class="description">启用后将显示中间跳转页面，否则直接跳转到目标网址。</p>',
            (isset($this->options['use_redirect_page']) && $this->options['use_redirect_page']) ? 'checked' : ''
        );
    }

    public function add_nofollow_callback() {
        printf(
            '<input type="checkbox" name="lebase64url_options[add_nofollow]" %s />'
            . '<p class="description">为外部链接添加nofollow属性。</p>',
            (isset($this->options['add_nofollow']) && $this->options['add_nofollow']) ? 'checked' : ''
        );
    }

    public function add_blank_callback() {
        printf(
            '<input type="checkbox" name="lebase64url_options[add_blank]" %s />'
            . '<p class="description">为外部链接添加_blank属性，在新窗口中打开链接。</p>',
            (isset($this->options['add_blank']) && $this->options['add_blank']) ? 'checked' : ''
        );
    }

    public function add_rewrite_rules() {
        add_rewrite_rule(
            'go/([^/]+)/?$',
            'index.php?lebase64url_redirect=$matches[1]',
            'top'
        );
        
        // 如果规则改变了，刷新重写规则
        if (get_option('lebase64url_rewrite_rules_flushed') != '1') {
            flush_rewrite_rules();
            update_option('lebase64url_rewrite_rules_flushed', '1');
        }
    }

    public function add_query_vars($query_vars) {
        $query_vars[] = 'lebase64url_redirect';
        return $query_vars;
    }
}

// 初始化插件
$lebase64url = LeBase64URL::get_instance();

// 注册激活、停用和卸载钩子
register_activation_hook(__FILE__, array($lebase64url, 'activate'));
register_deactivation_hook(__FILE__, array($lebase64url, 'deactivate'));
register_uninstall_hook(__FILE__, array($lebase64url, 'uninstall'));