# LeBase64URL

一个适用于 WordPress 的外链跳转插件，将文章中的外部链接转换为 Base64 加密格式，支持 nofollow 属性、白名单设置和多种跳转方式。

## 功能特性

- **Base64 加密跳转**：将文章中的外部链接自动转换为加密 URL，保护真实链接地址
- **灵活跳转方式**：支持直接跳转或显示中间确认页面
- **白名单机制**：可设置排除域名，白名单内的链接保持原样
- **SEO 友好**：可选为外链添加 `nofollow` 属性
- **新窗口打开**：可选为外链添加 `target="_blank"` 及 `rel="noopener noreferrer"` 安全属性
- **自定义 URL 前缀**：可配置加密链接的前缀格式

## 系统要求

- WordPress 4.5.0 或更高版本
- PHP 5.6 或更高版本

## 安装方法

1. 将 `LeBase64URL` 文件夹上传到 `/wp-content/plugins/` 目录
2. 在 WordPress 后台 **插件** 列表中激活 LeBase64URL
3. 进入 **设置 → LeBase64URL设置** 进行配置

## 配置说明

| 选项 | 说明 |
|------|------|
| **启用插件** | 开启后，文章中的外链将自动转换为加密格式 |
| **URL 前缀** | 加密链接的前缀，默认为 `go.php?url=` |
| **白名单域名** | 每行一个域名，这些域名的链接不会被加密（如：`laojiang.me`） |
| **使用中间页面** | 启用后显示跳转确认页，否则直接跳转到目标网址 |
| **添加 nofollow 属性** | 为外部链接添加 `rel="nofollow"` |
| **新窗口打开** | 为外部链接添加 `target="_blank"` |

## 工作原理

1. 插件通过 `the_content` 过滤器扫描文章内容中的 `<a>` 标签
2. 识别外部链接（非本站、非相对路径、非白名单域名）
3. 将目标 URL 进行 Base64 编码，生成形如 `yoursite.com/wp-content/plugins/lebase64url/go.php?url=xxx` 的链接
4. 用户点击时，插件解码 URL 并执行跳转（直接跳转或显示确认页）

## 链接格式

- **传统方式**：`/wp-content/plugins/lebase64url/go.php?url=Base64编码的URL`
- **重写规则**：`/go/Base64编码的URL`（需启用固定链接）

## 插件团队和技术支持

[乐在云](https://www.lezaiyun.com/)（老蒋和他的伙伴们），本着资源共享原则，在运营网站过程中用到的或者是有需要用到的主题、插件资源，有选择的免费分享给广大的网友站长，希望能够帮助到你建站过程中提高效率。

感谢团队成员，以及网友提出的优化工具的建议，才有后续产品的不断迭代适合且满足用户需要。不能确保100%的符合兼容网站，我们也仅能做到在工作之余不断的接近和满足你的需要。

| 类目            | 信息                                                         |
| --------------- | ------------------------------------------------------------ |
| 插件更新地址    | https://www.lezaiyun.com/861.html                            |
| 团队成员        | [老蒋](https://www.laojiang.me/)、老赵、[CNJOEL](https://www.rakvps.com/)、木村 |
| 支持网站        | 乐在云、主机评价网、老蒋玩主机                               |
| 建站资源推荐    | [便宜VPS推荐](https://www.zhujipingjia.com/pianyivps.html)、[美国VPS推荐](https://www.zhujipingjia.com/uscn2gia.html)、[外贸建站主机](https://www.zhujipingjia.com/wordpress-hosting.html)、[SSL证书推荐](https://www.zhujipingjia.com/two-ssls.html)、[WordPress主机推荐](https://www.zhujipingjia.com/wpblog-host.html) |
| 提交WP官网（F） |                                                              |

![](wechat.png)

## 更新日志

### 1.0.0
- 首次发布

## 许可证

GPL v2 或更高版本 - [查看许可证](https://www.gnu.org/licenses/gpl-2.0.html)

## 作者

老蒋和他的小伙伴 | 公众号：**老蒋朋友圈**
