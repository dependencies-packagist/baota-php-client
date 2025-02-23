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
    protected function request(string $path, array $data = []): array
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
     * @param int    $id   网站ID
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
     * @param int    $id 网站ID
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
     * @param int    $id    网站ID
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
     * @param int     $id   网站ID
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
     * @param int    $id      网站ID
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
     * @param int    $id      网站ID
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

}
