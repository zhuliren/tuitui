<?php
/**
 * Created by PhpStorm.
 * User: duheyuan
 * Date: 2019/7/2
 * Time: 16:10
 */

namespace app\index\model;


use app\common\Model\PublicEnum;
use think\Model;

class MessageModel extends Model
{

    protected $appid;
    protected $secret;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->appid = PublicEnum::WX_APPID;
        $this->secret = PublicEnum::WX_SECRET;
    }

    /**
     * @param $oppenid   小程序开放id
     * @param $tmp_id    模板id
     * @param $form_id   form_id 7天唯一id
     * @param string $key1 支付文本
     * @param string $key2  时间
     * @param string $key3  支付金额
     * @param null $key4  订单号
     * @return bool|mixed
     * @time: 2019/7/2
     * @autor: duheyuan
     */
    public function sendMessage($oppenid,$tmp_id,$form_id, $notice = '', $time = '00-00-00 00:00:00', $price = '0.0', $order_id = null )
    {

        if (($oppenid != 0) || ($oppenid != null)) {
            $temp_msg = array(
                'touser' => "{$oppenid}",
                'template_id' => $tmp_id,
                'page' => "/pages/index/index",
                'form_id' => "{$form_id}",
                'data' => array(
                    'keyword1' => array(
                        'value' => "{$notice}",
                    ),
                    'keyword2' => array(
                        'value' => "{$time}",
                    ),
                    'keyword3' => array(
                        'value' => "{$price}",
                    ),
                    'keyword4' => array(
                        'value' => "{$order_id}",
                    ),
                ),
            );

            $res = $this->sendXcxTemplateMsg($temp_msg);

            return $res;
        }else{
            return false;
        }
    }

    /**
     * @param $data
     * @return mixed
     * @time: 2019/7/2
     * @autor: duheyuan
     * 发送模板消息
     */
    public function sendXcxTemplateMsg($data)
    {
        $access_token = $this->getToken($this->appid,$this->secret);
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$access_token}";
        $res =  curl_post($url, $data);

        return $res;
    }
    //  获取token
    public function getToken($appid,$secret){

        $url ="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
        $res = curl_get($url);
        $res = json_decode($res,1);
        if (isset($res['errcode'])){
            if ($res['errcode'] != 0){
                return json(['status'=>$res['errmsg'],'msg'=>'失败','data'=>'']);
            }
        }
        return $res['access_token'];
    }



}