# BaoTa Panel SDK for PHP.

[![GitHub Tag](https://img.shields.io/github/v/tag/dependencies-packagist/baota-php-client)](https://github.com/dependencies-packagist/baota-php-client/tags)
[![Total Downloads](https://img.shields.io/packagist/dt/baota/client?style=flat-square)](https://packagist.org/packages/baota/client)
[![Packagist Version](https://img.shields.io/packagist/v/baota/client)](https://packagist.org/packages/baota/client)
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/baota/client)](https://github.com/dependencies-packagist/baota-php-client)
[![Packagist License](https://img.shields.io/github/license/dependencies-packagist/baota-php-client)](https://github.com/dependencies-packagist/baota-php-client)

## Installation

You can install the package via [Composer](https://getcomposer.org/):

```bash
composer require baota/client
```

## Usage

```php
use BaoTa\Client;

$bt       = new Client(bt_uri: 'http://127.0.0.1:8888', bt_key: 'xxxxxxxxxxxxxxxx');
$response = $bt->getSystemTotal();
var_dump($response);
```

```php
use BaoTa\Client;

$bt       = new Client(bt_uri: 'http://127.0.0.1:8888', bt_key: 'xxxxxxxxxxxxxxxx');
$response = $bt->request('/plugin?action=a&name=deployment&s=SetupPackage', [
    'dname'       => $dname,
    'site_name'   => $site_name,
    'php_version' => $php_version,
]);
var_dump($response);
```

## Methods

### System

| Method         | URI                           | Description            |
|----------------|-------------------------------|------------------------|
| getSystemTotal | /system?action=GetSystemTotal | 获取系统基础统计               |
| getDiskInfo    | /system?action=GetDiskInfo    | 获取磁盘分区信息               |
| getNetWork     | /system?action=GetNetWork     | 获取实时状态信息(CPU、内存、网络、负载) |
| getTaskCount   | /ajax?action=GetTaskCount     | 检查是否有安装任务              |
| updatePanel    | /ajax?action=UpdatePanel      | 检查面板更新                 |

### Sites

| Method            | URI                               | Description      |
|-------------------|-----------------------------------|------------------|
| websites          | /data?action=getData&table=sites  | 获取网站列表           |
| webtypes          | /site?action=get_site_types       | 获取网站分类           |
| getPHPVersion     | /site?action=GetPHPVersion        | 获取已安装的 PHP 版本列表  |
| getSitePHPVersion | /site?action=GetSitePHPVersion    | 获取指定网站运行的 PHP 版本 |
| setPHPVersion     | /site?action=SetPHPVersion        | 修改指定网站的 PHP 版本   |
| setHasPwd         | /site?action=SetHasPwd            | 开启并设置网站密码访问      |
| closeHasPwd       | /site?action=CloseHasPwd          | 关闭网站密码访问         |
| getDirUserINI     | /site?action=GetDirUserINI        | 获取网站几项开关         |
| webAddSite        | /site?action=AddSite              | 创建网站             |
| webDeleteSite     | /site?action=DeleteSite           | 删除网站             |
| webSiteStop       | /site?action=SiteStop             | 停用网站             |
| webSiteStart      | /site?action=SiteStart            | 启用网站             |
| webSetEdate       | /site?action=SetEdate             | 设置网站有效期          |
| webSetPs          | /data?action=setPs&table=sites    | 修改网站备注           |
| webBackupList     | /data?action=getData&table=backup | 获取网站备份列表         |
| webToBackup       | /site?action=ToBackup             | 创建网站备份           |
| webDelBackup      | /site?action=DelBackup            | 删除网站备份           |
| webDoaminList     | /data?action=getData&table=domain | 获取网站域名列表         |
| getDirBinding     | /site?action=GetDirBinding        | 获取网站域名绑定二级目录信息   |
| addDirBinding     | /site?action=AddDirBinding        | 添加网站子目录域名        |
| delDirBinding     | /site?action=DelDirBinding        | 删除网站绑定子目录        |
| getDirRewrite     | /site?action=GetDirRewrite        | 获取网站子目录伪静态规则     |
| webAddDomain      | /site?action=AddDomain            | 添加网站域名           |
| webDelDomain      | /site?action=DelDomain            | 删除网站域名           |
| getSiteLogs       | /site?action=GetSiteLogs          | 获取网站日志           |
| getSecurity       | /site?action=GetSecurity          | 获取网站盗链状态及规则信息    |
| setSecurity       | /site?action=SetSecurity          | 设置网站盗链状态及规则信息    |
| getSSL            | /site?action=GetSSL               | 获取 SSL 状态及证书详情   |
| setSSL            | /site?action=SetSSL               | 设置 SSL 证书        |
| httpToHttps       | /site?action=HttpToHttps          | 强制 HTTPS         |
| closeToHttps      | /site?action=CloseToHttps         | 关闭强制 HTTPS       |
| closeSSLConf      | /site?action=CloseSSLConf         | 关闭 SSL           |
| webGetIndex       | /site?action=GetIndex             | 获取网站默认文件         |
| webSetIndex       | /site?action=SetIndex             | 设置网站默认文件         |
| getLimitNet       | /site?action=GetLimitNet          | 获取网站流量限制信息       |
| setLimitNet       | /site?action=SetLimitNet          | 设置网站流量限制信息       |
| closeLimitNet     | /site?action=CloseLimitNet        | 关闭网站流量限制         |
| get301Status      | /site?action=Get301Status         | 获取网站 301 重定向信息   |
| set301Status      | /site?action=Set301Status         | 设置网站 301 重定向信息   |
| getRewriteList    | /site?action=GetRewriteList       | 获取可选的预定义伪静态列表    |
| getFileBody       | /files?action=GetFileBody         | 获取指定预定义伪静态规则内容   |
| saveFileBody      | /files?action=SaveFileBody        | 保存伪静态规则内容        |
| getProxyList      | /site?action=GetProxyList         | 获取网站反代信息及状态      |
| createProxy       | /site?action=CreateProxy          | 添加网站反代信息         |
| modifyProxy       | /site?action=ModifyProxy          | 修改网站反代信息         |

### Ftp

| Method          | URI                             | Description |
|-----------------|---------------------------------|-------------|
| webFtpList      | /data?action=getData&table=ftps | 获取 FTP 信息列表 |
| setUserPassword | /ftp?action=SetUserPassword     | 修改 FTP 账号密码 |
| setStatus       | /ftp?action=SetStatus           | 启用/禁用       |

### DBM

| Method          | URI                                  | Description |
|-----------------|--------------------------------------|-------------|
| webSqlList      | /data?action=getData&table=databases | 获取 SQL 信息列表 |
| resDatabasePass | /database?action=ResDatabasePassword | 修改 SQL 账号密码 |
| SQLToBackup     | /database?action=ToBackup            | 创建 SQL 备份   |
| SQLDelBackup    | /database?action=DelBackup           | 删除 SQL 备份   |

### Plugin

| Method       | URI                                     | Description |
|--------------|-----------------------------------------|-------------|
| deployment   | /deployment?action=GetList&type=&search | 宝塔一键部署列表    |
| setupPackage | /deployment?action=SetupPackage         | 部署任务        |

## License

Nacosvel Contracts is made available under the MIT License (MIT). Please see [License File](LICENSE) for more information.
