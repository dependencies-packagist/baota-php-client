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
    protected function GetKeyData(array $data): array
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
                'form_params' => $this->GetKeyData($data),
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
    public function GetSystemTotal(): array
    {
        return $this->request('/system?action=GetSystemTotal');
    }

    /**
     * 获取磁盘分区信息
     *
     * @return array
     */
    public function GetDiskInfo(): array
    {
        return $this->request('/system?action=GetDiskInfo');
    }

    /**
     * 获取实时状态信息(CPU、内存、网络、负载)
     *
     * @return array
     */
    public function GetNetWork(): array
    {
        return $this->request('/system?action=GetNetWork');
    }

    /**
     * 检查是否有安装任务
     *
     * @return array
     */
    public function GetTaskCount(): array
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
    public function UpdatePanel(bool $check = false, bool $force = false): array
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
    public function WebSites(int $page = 1, int $limit = 15, int $type = -1, string $order = 'id desc', string $tojs = '', string $search = ''): array
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
    public function WebTypes(): array
    {
        return $this->request('/site?action=get_site_types');
    }


    /**
     * 获取已安装的 PHP 版本列表
     *
     * @return array
     */
    public function GetPHPVersion(): array
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
    public function GetSitePHPVersion(string $siteName): array
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
    public function SetPHPVersion(string $siteName, string $version): array
    {
        return $this->request('/site?action=SetPHPVersion', [
            'siteName' => $siteName,
            'version'  => $version,
        ]);
    }

}
