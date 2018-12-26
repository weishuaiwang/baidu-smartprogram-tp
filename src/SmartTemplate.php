<?php

namespace baidu;

use think\Db;
use app\common\library\Curl;

trait SmartTemplate
{
    /**
     * [GET] 模板列表
     *
     **/
    public function gettemplatelist($page = 1, $page_size = 10)
    {
        $access_token = $this->getTpAccessToken();
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/template/gettemplatelist?access_token='.$access_token.'&page='.$page.'&page_size='.$page_size;
        $curl = new Curl;
        $data = $curl->get($url);

        return json_decode($data, true);
    }

    /**
     * [POST] 删除模板
     *
     **/
    public function deltemplate($template_id)
    {
        $access_token = $this->getTpAccessToken();
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/template/deltemplate?access_token='.$access_token.'&template_id='.$template_id;
    }

    /**
     * [GET] 模板草稿列表
     *
     **/
    public function gettemplatedraftlist($page = 1, $page_size = 50)
    {
        $access_token = $this->getTpAccessToken();
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/template/gettemplatedraftlist?access_token='.$access_token.'&page='.$page.'&page_size='.$page_size;

        $curl = new Curl;
        $data = $curl->get($url);

        return json_decode($data, true);
    }

    /**
     * [POST] 添加草稿至模板
     *
     **/
    public function addtotemplate($draft_id, $user_desc)
    {
        $access_token = $this->getTpAccessToken();
        $data['user_desc'] = $user_desc;
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/template/addtotemplate?access_token='.$access_token.'&draft_id='.$draft_id;
    }
}