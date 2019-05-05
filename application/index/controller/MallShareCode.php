<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/30
 * Time: 15:13
 */

namespace app\index\controller;


use think\Db;

class MallShareCode
{
    public function goodssharercode()
    {
        $goods_id = $_REQUEST['goodsid'];
        $upid = $_REQUEST['upid'];
        //判断数据库是否存在相同二维码
        $rcodedata = Db::table('ml_tbl_rcode')->where('goods_id', $goods_id)->where('upid', $upid)->find();
        if ($rcodedata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('rcodeurl' => $rcodedata['url']));
        } else {
            $fielname = rand(100, 99999) . $goods_id . $upid . '.png';
            // 为二维码创建一个文件
            $fiel = $_SERVER['DOCUMENT_ROOT'] . '/ttgoodssharercode/' . $fielname;
            //获取access_token
            $appid = 'wx0fda8074ccdb716d';
            $srcret = 'bf55d7a720d5bc162621e3901b7645be';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $srcret;
            // get请求获取access_token
            $data = $this->getCurl($url);
            $data = json_decode($data, true);
            //获取二维码
            //参数
            $postdata['scene'] = "goodsid=" . $goods_id . ",upid=" . $upid;
            // 宽度
            $postdata['width'] = 430;
            // 页面
            $postdata['page'] = 'packageA/details/details';
            // 线条颜色
            $postdata['auto_color'] = false;
            //auto_color 为 false 时生效
            $postdata['line_color'] = ['r' => '0', 'g' => '0', 'b' => '0'];
            // 是否有底色为true时是透明的
            $postdata['is_hyaline'] = false;
            $post_data = json_encode($postdata);
            // 获取二维码
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $data['access_token'];
            // post请求
            $result = $this->postCurl($url, $post_data);
            // 保存二维码
            file_put_contents($fiel, $result);
            $fileurl = 'https://tuitui.tango007.com/ttgoodssharercode/' . $fielname;
            $intodata = array('goods_id' => $goods_id, 'upid' => $upid, 'url' => $fileurl);
            Db::table('ml_tbl_rcode')->insert($intodata);
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('rcodeurl' => $fileurl));
        }
        return json($data);
    }

    function getCurl($url)
    {
        $info = curl_init();
        curl_setopt($info, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($info, CURLOPT_HEADER, 0);
        curl_setopt($info, CURLOPT_NOBODY, 0);
        curl_setopt($info, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($info, CURLOPT_URL, $url);
        $output = curl_exec($info);
        curl_close($info);
        return $output;
    }


    function postCurl($url, $data)
    {
        $ch = curl_init();
        $header[] = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        } else {
            return $tmpInfo;
        }
    }

}