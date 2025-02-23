<?php

namespace BaoTa;

use GuzzleHttp\Client as Http;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;

/**
 * 宝塔面板站点操作类库
 *
 * @link    https://www.bt.cn/api-doc.pdf
 * @example 获取系统基础统计
 * ```
 * $bt = new \BaoTa\Client('http://127.0.0.1/8888','xxxxxxxxxxxxxxxx')
 * var_dump($bt->GetSystemTotal());
 * ```
 */
class Client
{
    /**
     * 初始化
     *
     * @param string $bt_uri
     * @param string $bt_key
     */
    public function __construct(private string $bt_uri, private string $bt_key)
    {
        //
    }

    /**
     * 构造带有签名的关联数组
     *
     * @param array $data
     *
     * @return array
     */
    protected function getKeyData(array $data): array
    {
        $now = time();
        return [
                'request_token' => md5($now . md5($this->bt_key)),
                'request_time'  => $now,
            ] + $data;
    }

    /**
     * 请统一使用 POST 方式请求 API 接口，并在每次请求时附上 cookie
     *
     * @param string $path
     * @param array  $data
     *
     * @return array
     */
    public function request(string $path, array $data = []): array
    {
        $client = new Http([
            'base_uri' => $this->bt_uri,
        ]);

        try {
            $response = $client->request('POST', $path, [
                'form_params' => $this->getKeyData($data),
                'cookies'     => new CookieJar(),
            ]);
        } catch (GuzzleException $e) {
            return ['code' => $e->getCode(), 'error' => $e->getMessage()];
        }

        $code    = $response->getStatusCode();
        $message = $response->getReasonPhrase();

        if ($response->getStatusCode() >= 400) {
            return ['code' => $code, 'error' => $message];
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return compact('code', 'message', 'data');
        }

        return ['code' => json_last_error(), 'error' => json_last_error_msg()];
    }

    /**
     * 获取系统基础统计
     *
     * @return array
     */
    public function getSystemTotal(): array
    {
        return $this->request('/system?action=GetSystemTotal');
    }

    /**
     * 获取磁盘分区信息
     *
     * @return array
     */
    public function getDiskInfo(): array
    {
        return $this->request('/system?action=GetDiskInfo');
    }

    /**
     * 获取实时状态信息(CPU、内存、网络、负载)
     *
     * @return array
     */
    public function getNetWork(): array
    {
        return $this->request('/system?action=GetNetWork');
    }

    /**
     * 检查是否有安装任务
     *
     * @return array
     */
    public function getTaskCount(): array
    {
        return $this->request('/ajax?action=GetTaskCount');
    }

    /**
     * 检查面板更新
     *
     * @param bool $check
     * @param bool $force
     *
     * @return array
     */
    public function updatePanel(bool $check = false, bool $force = false): array
    {
        return $this->request('/ajax?action=UpdatePanel', ['check' => $check, 'force' => $force]);
    }

    /**
     * 获取网站列表
     *
     * @param int    $page   当前分页
     * @param int    $limit  取出的数据行数
     * @param int    $type   分类标识 -1: 分部分类 0: 默认分类
     * @param string $order  排序规则 使用 id 降序：id desc 使用名称升序：name desc
     * @param string $tojs   分页 JS 回调,若不传则构造 URI 分页连接
     * @param string $search 搜索内容
     *
     * @return array
     */
    public function webSites(int $page = 1, int $limit = 15, int $type = -1, string $order = 'id desc', string $tojs = '', string $search = ''): array
    {
        return $this->request('/data?action=getData&table=sites', [
            'p'      => $page,
            'limit'  => $limit,
            'type'   => $type,
            'order'  => $order,
            'tojs'   => $tojs,
            'search' => $search,
        ]);
    }

    /**
     * 获取所有网站分类
     *
     * @return array
     */
    public function webTypes(): array
    {
        return $this->request('/site?action=get_site_types');
    }

    /**
     * 获取已安装的 PHP 版本列表
     *
     * @return array
     */
    public function getPHPVersion(): array
    {
        return $this->request('/site?action=GetPHPVersion');
    }

    /**
     * 获取指定网站运行的 PHP 版本
     *
     * @param string $siteName
     *
     * @return array
     */
    public function getSitePHPVersion(string $siteName): array
    {
        return $this->request('/site?action=GetSitePHPVersion', [
            'siteName' => $siteName,
        ]);
    }

    /**
     * 修改指定网站的 PHP 版本
     *
     * @param string $siteName
     * @param string $version
     *
     * @return array
     */
    public function setPHPVersion(string $siteName, string $version): array
    {
        return $this->request('/site?action=SetPHPVersion', [
            'siteName' => $siteName,
            'version'  => $version,
        ]);
    }

    /**
     * 设置密码访问网站
     *
     * @param int    $id
     * @param string $username
     * @param string $password
     *
     * @return array
     */
    public function setHasPwd(int $id, string $username, string $password): array
    {
        return $this->request('/site?action=SetHasPwd', [
            'id'       => $id,
            'username' => $username,
            'password' => $password,
        ]);
    }

    /**
     * 关闭密码访问网站
     *
     * @param int $id
     *
     * @return array
     */
    public function closeHasPwd(int $id): array
    {
        return $this->request('/site?action=CloseHasPwd', [
            'id' => $id,
        ]);
    }

    /**
     * 获取网站三项配置开关（防跨站、日志、密码访问）
     *
     * @param int    $id
     * @param string $path
     *
     * @return array
     */
    public function getDirUserINI(int $id, string $path): array
    {
        return $this->request('/site?action=GetDirUserINI', [
            'id'   => $id,
            'path' => $path,
        ]);
    }

    /**
     * 新增网站
     *
     * @param string $webname      网站域名 json格式
     * @param string $path         网站路径
     * @param int    $type_id      网站分类ID
     * @param string $type         网站类型
     * @param string $version      PHP版本
     * @param int    $port         网站端口
     * @param string $ps           网站备注
     * @param string $ftp          网站是否开通FTP
     * @param string $ftp_username FTP用户名
     * @param string $ftp_password FTP密码
     * @param string $sql          网站是否开通数据库
     * @param string $codeing      数据库编码类型 utf8|utf8mb4|gbk|big5
     * @param string $dataUser     数据库账号
     * @param string $dataPassword 数据库密码
     *
     * @return array
     */
    public function addSite(
        string $webname,
        string $path,
        int    $type_id,
        string $type,
        string $version,
        int    $port,
        string $ps,
        string $ftp = 'false',
        string $ftp_username = '',
        string $ftp_password = '',
        string $sql = 'false',
        string $codeing = 'utf8mb4',
        string $dataUser = '',
        string $dataPassword = '',
    ): array
    {
        return $this->request('/site?action=AddSite', [
            'webname'      => $webname,
            'path'         => $path,
            'type_id'      => $type_id,
            'type'         => $type,
            'version'      => $version,
            'port'         => $port,
            'ps'           => $ps,
            'ftp'          => $ftp,
            'ftp_username' => $ftp_username,
            'ftp_password' => $ftp_password,
            'sql'          => $sql,
            'codeing'      => $codeing,
            'datauser'     => $dataUser,
            'datapassword' => $dataPassword,
        ]);
    }

    /**
     * 删除网站
     *
     * @param int    $id
     * @param string $webname
     * @param string $ftp
     * @param string $database
     * @param string $path
     *
     * @return array
     */
    public function webDeleteSite(int $id, string $webname, string $ftp = '', string $database = '', string $path = ''): array
    {
        return $this->request('/site?action=DeleteSite', [
            'id'       => $id,
            'webname'  => $webname,
            'ftp'      => $ftp,
            'database' => $database,
            'path'     => $path,
        ]);
    }

    /**
     * 停用站点
     *
     * @param int    $id
     * @param string $name
     *
     * @return array
     */
    public function webSiteStop(int $id, string $name): array
    {
        return $this->request('/site?action=SiteStop', [
            'id'   => $id,
            'name' => $name,
        ]);
    }

    /**
     * 启用网站
     *
     * @param int    $id   网站 ID
     * @param string $name 网站域名
     *
     * @return array
     */
    public function webSiteStart(int $id, string $name): array
    {
        return $this->request('/site?action=SiteStart', [
            'id'   => $id,
            'name' => $name,
        ]);
    }

    /**
     * 设置网站到期时间
     *
     * @param int    $id   网站 ID
     * @param string $date 网站到期时间 格式：2019-01-01，永久：0000-00-00
     *
     * @return array
     */
    public function webSetEdate(int $id, string $date): array
    {
        return $this->request('/site?action=SetEdate', [
            'id'    => $id,
            'edate' => $date,
        ]);
    }

    /**
     * 修改网站备注
     *
     * @param int    $id 网站 ID
     * @param string $ps 网站备注
     *
     * @return array
     */
    public function webSetPs(int $id, string $ps): array
    {
        return $this->request('/data?action=setPs&table=sites', [
            'id' => $id,
            'ps' => $ps,
        ]);
    }

    /**
     * 获取网站备份列表
     *
     * @param int    $id    网站 ID
     * @param string $page  当前分页
     * @param string $limit 每页取出的数据行数
     * @param string $type  备份类型 目前固定为0
     * @param string $tojs  分页js回调若不传则构造 URI 分页连接 get_site_backup
     *
     * @return array
     */
    public function webBackupList(int $id, string $page = '1', string $limit = '5', string $type = '0', string $tojs = ''): array
    {
        return $this->request('/data?action=getData&table=backup', [
            'id'     => $id,
            'p'      => $page,
            'limit'  => $limit,
            'type'   => $type,
            'tojs'   => $tojs,
            'search' => $id,
        ]);
    }

    /**
     * 创建网站备份
     *
     * @param int $id 网站 ID
     *
     * @return array
     */
    public function webToBackup(int $id): array
    {
        return $this->request('/site?action=ToBackup', [
            'id' => $id,
        ]);
    }

    /**
     * 删除网站备份
     *
     * @param int $id 网站 ID
     *
     * @return array
     */
    public function WebDelBackup(int $id): array
    {
        return $this->request('/site?action=DelBackup', [
            'id' => $id,
        ]);
    }

    /**
     * 获取网站域名列表
     *
     * @param int     $id   网站 ID
     * @param boolean $list 固定传 true
     *
     * @return array
     */
    public function webDomainList(int $id, bool $list = true): array
    {
        return $this->request('/data?action=getData&table=domain', [
            'search' => $id,
            'list'   => $list,
        ]);
    }

    /**
     * 获取网站域名绑定二级目录信息
     *
     * @param int $id 网站 ID
     *
     * @return array
     */
    public function getDirBinding(int $id): array
    {
        return $this->request('/site?action=GetDirBinding', [
            'id' => $id,
        ]);
    }

    /**
     * 设置网站域名绑定二级目录
     *
     * @param int    $id      网站 ID
     * @param string $domain  域名
     * @param string $dirName 目录
     *
     * @return array
     */
    public function addDirBinding(int $id, string $domain, string $dirName): array
    {
        return $this->request('/site?action=AddDirBinding', [
            'id'      => $id,
            'domain'  => $domain,
            'dirName' => $dirName,
        ]);
    }

    /**
     * 删除网站域名绑定二级目录
     *
     * @param int $id 子目录 ID
     *
     * @return array
     */
    public function delDirBinding(int $id): array
    {
        return $this->request('/site?action=DelDirBinding', [
            'id' => $id,
        ]);
    }

    /**
     * 获取网站子目录绑定伪静态信息
     *
     * @param int $id 子目录绑定ID
     * @param int $type
     *
     * @return array
     */
    public function getDirRewrite(int $id, int $type = 0): array
    {
        $data = [
            'id' => $id,
        ];
        if ($type) {
            $data['add'] = 1;
        }
        return $this->request('/site?action=GetDirRewrite', $data);
    }

    /**
     * 添加域名
     *
     * @param int    $id      网站 ID
     * @param string $webname 网站名称
     * @param string $domain  要添加的域名:端口 80 端口不必构造端口,多个域名用换行符隔开
     *
     * @return array
     */
    public function webAddDomain(int $id, string $webname, string $domain): array
    {
        return $this->request('/site?action=AddDomain', [
            'id'      => $id,
            'webname' => $webname,
            'domain'  => $domain,
        ]);
    }

    /**
     * 删除网站域名
     *
     * @param int    $id      网站 ID
     * @param string $webname 网站名
     * @param string $domain  网站域名
     * @param int    $port    网站域名端口
     *
     * @return array
     */
    public function webDelDomain(int $id, string $webname, string $domain, int $port): array
    {
        return $this->request('/site?action=DelDomain', [
            'id'      => $id,
            'webname' => $webname,
            'domain'  => $domain,
            'port'    => $port,
        ]);
    }

    /**
     * 获取网站日志
     *
     * @param string $siteName 网站名
     *
     * @return array
     */
    public function getSiteLogs(string $siteName): array
    {
        return $this->request('/site?action=GetSiteLogs', [
            'siteName' => $siteName,
        ]);
    }

    /**
     * 获取网站盗链状态及规则信息
     *
     * @param int    $id   网站 ID
     * @param string $name 网站名
     *
     * @return array
     */
    public function getSecurity(int $id, string $name): array
    {
        return $this->request('/site?action=GetSecurity', [
            'id'   => $id,
            'name' => $name,
        ]);
    }

    /**
     * 设置网站盗链状态及规则信息
     *
     * @param int    $id      网站 ID
     * @param string $name    网站名
     * @param string $fix     URL后缀
     * @param string $domains 许可域名
     * @param string $status  状态
     *
     * @return array
     */
    public function setSecurity(int $id, string $name, string $fix, string $domains, string $status): array
    {
        return $this->request('/site?action=SetSecurity', [
            'id'      => $id,
            'name'    => $name,
            'fix'     => $fix,
            'domains' => $domains,
            'status'  => $status,
        ]);
    }

    /**
     * 获取SSL状态及证书信息
     *
     * @param string $siteName 域名（纯域名）
     *
     * @return array
     */
    public function getSSL(string $siteName): array
    {
        return $this->request('/site?action=GetSSL', [
            'siteName' => $siteName,
        ]);
    }

    /**
     * 设置SSL域名证书
     *
     * @param string $type     类型
     * @param string $siteName 网站名
     * @param string $key      证书key
     * @param string $csr      证书PEM
     *
     * @return array
     */
    public function setSSL(string $type, string $siteName, string $key, string $csr): array
    {
        return $this->request('/site?action=SetSSL', [
            'type'     => $type,
            'siteName' => $siteName,
            'key'      => $key,
            'csr'      => $csr,
        ]);
    }

    /**
     * 关闭SSL
     *
     * @param string $updateOf 修改状态码
     * @param string $siteName 域名(纯域名)
     *
     * @return array
     */
    public function closeSSLConf(string $updateOf, string $siteName): array
    {
        return $this->request('/site?action=CloseSSLConf', [
            'updateOf' => $updateOf,
            'siteName' => $siteName,
        ]);
    }

    /**
     * 开启强制HTTPS
     *
     * @param string $siteName 网站域名（纯域名）
     *
     * @return array
     */
    public function httpToHttps(string $siteName): array
    {
        return $this->request('/site?action=HttpToHttps', [
            'siteName' => $siteName,
        ]);
    }

    /**
     * 关闭强制HTTPS
     *
     * @param string $siteName 域名(纯域名)
     *
     * @return array
     */
    public function closeToHttps(string $siteName): array
    {
        return $this->request('/site?action=CloseToHttps', [
            'siteName' => $siteName,
        ]);
    }

    /**
     * 获取网站默认文件
     *
     * @param int $id 网站 ID
     *
     * @return array
     */
    public function webGetIndex(int $id): array
    {
        return $this->request('/site?action=GetIndex', [
            'id' => $id,
        ]);
    }

    /**
     * 设置网站默认文件
     *
     * @param int    $id    网站 ID
     * @param string $index 内容
     *
     * @return array
     */
    public function webSetIndex(int $id, string $index): array
    {
        return $this->request('/site?action=SetIndex', [
            'id'    => $id,
            'Index' => $index,
        ]);
    }

    /**
     * 获取网站流量限制信息
     *
     * @param int $id 网站 ID
     *
     * @return array
     */
    public function getLimitNet(int $id): array
    {
        return $this->request('/site?action=GetLimitNet', [
            'id' => $id,
        ]);
    }

    /**
     * 设置网站流量限制信息
     *
     * @param int    $id         网站 ID
     * @param string $perserver  并发限制
     * @param string $perip      单 IP 限制
     * @param string $limit_rate 流量限制
     *
     * @return array
     */
    public function setLimitNet(int $id, string $perserver, string $perip, string $limit_rate): array
    {
        return $this->request('/site?action=SetLimitNet', [
            'id'         => $id,
            'perserver'  => $perserver,
            'perip'      => $perip,
            'limit_rate' => $limit_rate,
        ]);
    }

    /**
     * 关闭网站流量限制
     *
     * @param int $id 网站 ID
     *
     * @return array
     */
    public function closeLimitNet(int $id): array
    {
        return $this->request('/site?action=CloseLimitNet', [
            'id' => $id,
        ]);
    }

    /**
     * 获取网站 301 重定向信息
     *
     * @param string $siteName 网站名
     *
     * @return array
     */
    public function get301Status(string $siteName): array
    {
        return $this->request('/site?action=Get301Status', [
            'siteName' => $siteName,
        ]);
    }

    /**
     * 设置网站 301 重定向信息
     *
     * @param string $siteName  网站名
     * @param string $toDomain  目标 Url
     * @param string $srcDomain 来自 Url
     * @param string $type      类型
     *
     * @return array
     */
    public function set301Status(string $siteName, string $toDomain, string $srcDomain, string $type): array
    {
        return $this->request('/site?action=Set301Status', [
            'siteName'  => $siteName,
            'toDomain'  => $toDomain,
            'srcDomain' => $srcDomain,
            'type'      => $type,
        ]);
    }

    /**
     * 获取可选的预定义伪静态列表
     *
     * @param string $siteName 网站名
     *
     * @return array
     */
    public function getRewriteList(string $siteName): array
    {
        return $this->request('/site?action=GetRewriteList', [
            'siteName' => $siteName,
        ]);
    }

    /**
     * 获取预置伪静态规则内容（文件内容）
     *
     * @param string $path 规则名
     * @param int    $type 0->获取内置伪静态规则；1->获取当前站点伪静态规则
     *
     * @return array
     */
    public function getFileBody(string $path, int $type = 0): array
    {
        $path_dir = $type ? 'vhost/rewrite' : 'rewrite/nginx';

        // // 获取当前站点伪静态规则
        // /www/server/panel/vhost/rewrite/user_hvVBT_1.test.com.conf
        // // 获取内置伪静态规则
        // /www/server/panel/rewrite/nginx/EmpireCMS.conf
        // // 保存伪静态规则到站点
        // /www/server/panel/vhost/rewrite/user_hvVBT_1.test.com.conf
        // /www/server/panel/rewrite/nginx/typecho.conf
        $path = '/www/server/panel/' . $path_dir . '/' . $path . '.conf';
        return $this->request('/files?action=GetFileBody', [
            'path' => $path,
        ]);
    }

    /**
     * 保存伪静态规则内容(保存文件内容)
     *
     * @param string $path     规则名
     * @param string $data     规则内容
     * @param string $encoding 规则编码强转utf-8
     * @param int    $type     0->系统默认路径；1->自定义全路径
     *
     * @return array
     */
    public function saveFileBody(string $path, string $data, string $encoding = 'utf-8', int $type = 0): array
    {
        if ($type) {
            $path_dir = $path;
        } else {
            $path_dir = '/www/server/panel/vhost/rewrite/' . $path . '.conf';
        }
        return $this->request('/files?action=SaveFileBody', [
            'path'     => $path_dir,
            'data'     => $data,
            'encoding' => $encoding,
        ]);
    }

    /**
     * 获取网站反代信息及状态
     *
     * @param string $siteName
     *
     * @return array
     */
    public function getProxyList(string $siteName): array
    {
        return $this->request('/site?action=GetProxyList', [
            'siteName' => $siteName,
        ]);
    }

    /**
     * 添加网站反代信息
     *
     * @param string $cache     是否缓存
     * @param string $proxyname 代理名称
     * @param string $cachetime 缓存时长 /小时
     * @param string $proxydir  代理目录
     * @param string $proxysite 反代 URL
     * @param string $todomain  目标域名
     * @param string $advanced  高级功能：开启代理目录
     * @param string $sitename  网站名
     * @param string $subfilter 文本替换json格式[{"sub1":"百度","sub2":"白底"},{"sub1":"","sub2":""}]
     * @param int    $type      开启或关闭 0关;1开
     *
     * @return array
     */
    public function createProxy(
        string $cache,
        string $proxyname,
        string $cachetime,
        string $proxydir,
        string $proxysite,
        string $todomain,
        string $advanced,
        string $sitename,
        string $subfilter,
        int    $type
    ): array
    {
        return $this->request('/site?action=CreateProxy', [
            'cache'     => $cache,
            'proxyname' => $proxyname,
            'cachetime' => $cachetime,
            'proxydir'  => $proxydir,
            'proxysite' => $proxysite,
            'todomain'  => $todomain,
            'advanced'  => $advanced,
            'sitename'  => $sitename,
            'subfilter' => $subfilter,
            'type'      => $type,
        ]);
    }

    /**
     * 修改网站反代信息
     *
     * @param string $cache     是否缓存
     * @param string $proxyname 代理名称
     * @param string $cachetime 缓存时长 /小时
     * @param string $proxydir  代理目录
     * @param string $proxysite 反代URL
     * @param string $todomain  目标域名
     * @param string $advanced  高级功能：开启代理目录
     * @param string $sitename  网站名
     * @param string $subfilter 文本替换json格式[{"sub1":"百度","sub2":"白底"},{"sub1":"","sub2":""}]
     * @param int    $type      开启或关闭 0关;1开
     *
     * @return array
     */
    public function modifyProxy(
        string $cache,
        string $proxyname,
        string $cachetime,
        string $proxydir,
        string $proxysite,
        string $todomain,
        string $advanced,
        string $sitename,
        string $subfilter,
        int    $type
    ): array
    {
        return $this->request('/site?action=ModifyProxy', [
            'cache'     => $cache,
            'proxyname' => $proxyname,
            'cachetime' => $cachetime,
            'proxydir'  => $proxydir,
            'proxysite' => $proxysite,
            'todomain'  => $todomain,
            'advanced'  => $advanced,
            'sitename'  => $sitename,
            'subfilter' => $subfilter,
            'type'      => $type,
        ]);
    }

    /**
     * 获取网站 FTP 列表
     *
     * @param string $page   当前分页
     * @param string $limit  取出的数据行数
     * @param string $type   分类标识 -1: 分部分类 0: 默认分类
     * @param string $order  排序规则 使用 id 降序：id desc 使用名称升序：name desc
     * @param string $tojs   分页 JS 回调,若不传则构造 URI 分页连接
     * @param string $search 搜索内容
     *
     * @return array
     */
    public function webFtpList(string $page = '1', string $limit = '15', string $type = '-1', string $order = 'id desc', string $tojs = '', string $search = ''): array
    {
        return $this->request('/data?action=getData&table=ftps', [
            'p'      => $page,
            'limit'  => $limit,
            'type'   => $type,
            'order'  => $order,
            'tojs'   => $tojs,
            'search' => $search,
        ]);
    }

    /**
     * 修改 FTP 账号密码
     *
     * @param int    $id
     * @param string $ftp_username 用户名
     * @param string $new_password 密码
     *
     * @return array
     */
    public function setUserPassword(int $id, string $ftp_username, string $new_password): array
    {
        return $this->request('/ftp?action=SetUserPassword', [
            'id'           => $id,
            'ftp_username' => $ftp_username,
            'new_password' => $new_password,
        ]);
    }

    /**
     * 启用/禁用 FTP
     *
     * @param int    $id
     * @param string $username 用户名
     * @param int    $status   状态 0->关闭;1->开启
     *
     * @return array
     */
    public function setStatus(int $id, string $username, int $status): array
    {
        return $this->request('/ftp?action=SetStatus', [
            'id'       => $id,
            'username' => $username,
            'status'   => $status,
        ]);
    }

    /**
     * 获取网站 SQL 列表
     *
     * @param string $page   当前分页
     * @param string $limit  取出的数据行数
     * @param string $type   分类标识 -1: 分部分类 0: 默认分类
     * @param string $order  排序规则 使用 id 降序：id desc 使用名称升序：name desc
     * @param string $tojs   分页 JS 回调,若不传则构造 URI 分页连接
     * @param string $search 搜索内容
     *
     * @return array
     */
    public function webSqlList(string $page = '1', string $limit = '15', string $type = '-1', string $order = 'id desc', string $tojs = '', string $search = ''): array
    {
        return $this->request('/data?action=getData&table=databases', [
            'p'      => $page,
            'limit'  => $limit,
            'type'   => $type,
            'order'  => $order,
            'tojs'   => $tojs,
            'search' => $search,
        ]);
    }

    /**
     * 修改 SQL 账号密码
     *
     * @param int    $id
     * @param string $name     用户名
     * @param string $password 密码
     *
     * @return array
     */
    public function resDatabasePass(int $id, string $name, string $password): array
    {
        return $this->request('/database?action=ResDatabasePassword', [
            'id'       => $id,
            'name'     => $name,
            'password' => $password,
        ]);
    }

    /**
     * 备份数据库
     *
     * @param int $id 数据库列表 ID
     *
     * @return array
     */
    public function SQLToBackup(int $id): array
    {
        return $this->request('/database?action=ToBackup', [
            'id' => $id,
        ]);
    }

    /**
     * 删除数据库备份
     *
     * @param int $id 数据库备份 ID
     *
     * @return array
     */
    public function SQLDelBackup(int $id): array
    {
        return $this->request('/database?action=DelBackup', [
            'id' => $id,
        ]);
    }

    /**
     * 宝塔一键部署列表
     *
     * @param int    $type
     * @param string $search 搜索关键词
     *
     * @return array
     */
    public function deployment(int $type = 0, string $search = ''): array
    {
        return $this->request("/deployment?action=GetList&type={$type}" . ($search ? "&search={$search}" : ''));
    }

    /**
     * 宝塔一键部署执行
     *
     * @param string $dname       部署程序名
     * @param string $site_name   部署到网站名
     * @param string $php_version PHP版本
     */
    public function setupPackage(string $dname, string $site_name, string $php_version): array
    {
        return $this->request('/deployment?action=SetupPackage', [
            'dname'       => $dname,
            'site_name'   => $site_name,
            'php_version' => $php_version,
        ]);
    }

}
