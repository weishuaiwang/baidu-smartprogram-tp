<?php

namespace baidu;

use baidu\encoding\AesEncryptUtil;
use Curl\Curl;

class SmartProgramTP
{
    private $client_id, $aes_key, $tpkey;
    private $curl;

    public function __construct($client_id, $aes_key, $tpkey)
    {
        $this->client_id = $client_id;
        $this->aes_key = $aes_key;
        $this->tpkey = $tpkey;
        $this->curl = new Curl();
    }

    /**
     * 解析Ticket数据
     * 百度服务器每10分钟请求第三方服务器进行刷新此数据
     **/
    public function getTicket( $data )
    {
        $dataCoder = new AesEncryptUtil($this->client_id, $this->aes_key);
        $deData = $dataCoder->decrypt($data['Encrypt']);
        $ticketArr = json_decode($deData, true);

        $ticket = [
            'ticket' => $ticketArr['Ticket'],
            'origin_data' => $deData,
            'add_time' => date('Y-m-d H:i:s', $ticketArr['CreateTime'])
        ];

        return $ticket;
    }

    /**
     * 获取第三方平台access_token
     *
     * @param $ticket 第三方平台解析push数据后得到的ticket
     *
     **/
    public function getTpAccessToken($ticket)
    {
        $url = 'https://openapi.baidu.com/public/2.0/smartapp/auth/tp/token?client_id='.$this->tpkey.'&ticket='.$ticket;

        $json = $this->curl->get($url);
        if ($this->curl->error) {
            return [
                'errno' => 0,
                'msg' => $curl->errorMessage
            ];
        }

        $result = json_decode($json, true);
        if ($result['errno'] == 0) {
            return [
                'errno' => 1,
                'access_token' => $result['data']['access_token'],
                'msg' => 'success'
            ];
        }

        return [
            'errno' => 0,
            'msg' => isset($result['data']['msg']) ? $result['data']['msg'] : '接口请求失败';
        ];
    }

    /**
     * 3、获取预授权码pre_auth_code
     **/
    public function getPreAuthCode()
    {
        $pre_auth_code = '';
        $access_token = $this->getTpAccessToken();

        if ($access_token == '') {
            $this->error('ticket无效');
        }

        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/tp/createpreauthcode?access_token='.$access_token;
        $curl = new Curl();
        $json = $curl->get($url);

        $result = json_decode($json, true);

        if ($result['errno'] == 0) {
            $pre_auth_code = $result['data']['pre_auth_code'];

            $this->cache->set('baidu_tp_pre_auth_code', $pre_auth_code, 600);
        }
    }

    /**
     * 引导小程序管理员对第三方平台进行授权
     *
     **/
    public function authUrl($redirect_uri)
    {
        $pre_auth_code = $this->getPreAuthCode();

        if (!$pre_auth_code) {
            return '';
        }

        if (empty($redirect_uri)) {
            return '';
        }

        $url = 'https://smartprogram.baidu.com/mappconsole/tp/authorization?client_id='.$this->tpkey.'&redirect_uri=' . urlencode($redirect_uri) . '&pre_auth_code=' . $pre_auth_code;

        return $url;
    }

    /**
     * 授权回调后，获取access_token
     **/
    public function getAccessToken($authorization_code, $expires_in='')
    {
        $access_token = $this->getTpAccessToken();
        $url = 'https://openapi.baidu.com/rest/2.0/oauth/token?access_token='.$access_token.'&code='.$authorization_code.'&grant_type=app_to_tp_authorization_code';
        $curl = new Curl;
        $json = $curl->get($url);

        return json_decode($json, true);
    }

    /**
     * 刷新小程序access_token
     *
     **/
    public function refreshToken($miniappid, $refresh_token)
    {
        $access_token = $this->getTpAccessToken();

        $url = 'https://openapi.baidu.com/rest/2.0/oauth/token?access_token='.$access_token.'&refresh_token='.$refresh_token.'&grant_type=app_to_tp_refresh_token';
        $curl = new Curl;
        $json = $curl->get($url);
        $data = json_decode($json, true);

        if (isset($data['error'])) {
            file_put_contents(ROOT_PATH . 'baidu_smart_access_token.txt', $json, FILE_APPEND);
        } else {
            $update = [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expire_at' => time() + $data['expires_in'] - 1800
            ];

            Db::name('member_miniapp_baidu')->where('member_miniapp_id', $miniappid)->update($update);
        }

        return $data;
    }

    /**
     * 获取小程序基础信息
     **/
    public function getAppInfo($access_token)
    {
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/app/info?access_token='.$access_token;
        $curl = new Curl;
        $json = $curl->get($url);

        return json_decode($json, true);
    }

    /**
     * 获取客户百度小程序access_token
     **/
    public function getBaiduAppToken($miniappid)
    {
        $smart = Db::name('member_miniapp_baidu')->where('member_miniapp_id', $miniappid)->find();

        if ($smart['expire_at'] > time()) {
            return $smart['access_token'];
        } else {
            $data = $this->refreshToken($miniappid, $smart['refresh_token']);

            return $data->access_token;
        }
    }
}