<?php

namespace baidu;

use think\Db;
use app\common\library\Curl;

trait SmartPackage
{
    /**
     * [POST] 为授权的小程序帐号上传小程序代码
     **/
    public function smartUpload($miniappid, $data)
    {
        $access_token = $this->getBaiduAppToken($miniappid);
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/package/upload?access_token='.$access_token;
        $data = [
            'template_id' => $data['template_id'],
            'ext_json' => $data['ext_json'],
            'user_version' => $data['user_version'],
            'user_desc' => $data['user_desc'],
        ];

        $curl = new Curl;
        $json = $curl->post($url, $data);

        return json_decode($json, true);
    }

    /**
     * [POST] 为授权的小程序提交审核
     **/
    public function submitaudit()
    {
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/package/submitaudit?access_token=ACCESS_TOKEN';
    }

    /**
     * [POST] 发布已通过审核的小程序
     *
     **/
    public function release()
    {
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/package/release?access_token=ACCESS_TOKEN';
    }

    /**
     * [POST] 小程序版本回退
     *
     **/
    public function rollback()
    {
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/package/rollback?access_token=ACCESS_TOKEN';
    }

    /**
     * [POST] 小程序审核撤回
     *
     **/
    public function withdraw()
    {
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/package/withdraw?access_token=ACCESS_TOKEN';
    }

    /**
     * [GET] 获取授权小程序预览包详情
     *
     **/
    public function gettrial()
    {
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/package/gettrial?access_token=ACCESS_TOKEN';
    }

    /**
     * [GET] 获取小程序包列表
     **/
    public function get()
    {
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/package/get?access_token=ACCESS_TOKEN';
    }

    /**
     * [GET] 获取授权小程序包详情
     *
     **/
    public function getdetail()
    {
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/package/getdetail?access_token=ACCESS_TOKEN&type=TYPE&package_id=PACKAGE_ID';
    }
}