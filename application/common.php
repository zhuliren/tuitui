<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// 过滤掉emoji表情
function filter_Emoji($str)
{
    $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);

    return $str;
}

function preg_mobile($mobile) {
    if(preg_match("/^(13[0-9]|14[5|7|9]|15[0|1|2|3|5|6|7|8|9]|16[6]|17[0|1|2|3|5|6|7|8]|18[0-9]|19[8|9])\d{8}$/", $mobile)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function preg_id_card($id_card){
    if (preg_match("/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i",$id_card)){
        return true;
    }else{
        return false;
    }
}


//  随机订单号
function randomOrder_no()
{
    $time =date('Ymd',time());
    $rand_no = substr((lcg_value() * 100000000),0,8);
    $rand =  substr((lcg_value() * 10000),0,4);
    $res = $time.$rand_no.$rand;
    $num = rand(1,10);
    if (strstr($res,'.')){
        $res = str_replace('.',$num,$res);
    }
    return $res;
}
//  名字正则
function namePreg($name)
{
    if (isset($name) && !empty($name)){
        if (preg_match("/^[\x{4e00}-\x{9fa5}]{2,6}$/u",$name)) {
//                    print("中文");
            return true;
        } else {
            return false;
//                    print("非中文");
        }
    }
}


/**
 * 创建(导出)Excel数据表格
 * @param  array $list 要导出的数组格式的数据
 * @param  string $filename 导出的Excel表格数据表的文件名
 * @param  array $indexKey $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
 * @param int $startRow 第一条数据在Excel表格中起始行
 * @param bool $excel2007
 * @return bool
 * @throws PHPExcel_Exception
 * @throws PHPExcel_Writer_Exception
 */
function toExcel($list,$filename,$indexKey,$startRow=1,$excel2007=false){
    //文件引入
    include  '../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
    include  '../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
    include  '../vendor/phpoffice/phpexcel/Classes/PHPExcel/Writer/Excel2007.php';

    \think\Loader::import('PHPExcel.PHPExcel');
    \think\Loader::import('PHPExcel.PHPExcel.IOFactory');

    // Loader::import('PHPExcel.PHPExcel.Writer.Excel2007');
    $objPHPExcel = new PHPExcel();


    ob_end_clean();
    if(empty($filename)) $filename = time();
    if( !is_array($indexKey)) return false;

    $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    //初始化PHPExcel()

    //设置保存版本格式
    if($excel2007){
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $filename = $filename.'.xlsx';
    }else{
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $filename = $filename.'.xls';
    }

    //接下来就是写数据到表格里面去
    $objActSheet = $objPHPExcel->getActiveSheet();

    $objPHPExcel->setActiveSheetIndex(0)                                                                            // set table header content
                     ->setCellValue('A1', 'ID')
                     ->setCellValue('B1', '用户id')
                     ->setCellValue('C1', '提现订单单号')
                     ->setCellValue('D1', '提现金额')
                     ->setCellValue('E1', '提现人姓名')
                     ->setCellValue('F1', '银行卡号')
                     ->setCellValue('G1', '联系号码')
                     ->setCellValue('H1', '提现时间')
                     ->setCellValue('I1', '描述');
    $startRow = 2;
    foreach ($list as $row) {
        foreach ($indexKey as $key => $value){
            //这里是设置单元格的内容
            $objActSheet->setCellValueExplicit($header_arr[$key].$startRow,$row[$value]);
        }
        $startRow++;
    }

    // 下载这个表格，在浏览器输出
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/vnd.ms-execl");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");;
    header('Content-Disposition:attachment;filename='.$filename.'');
    header("Content-Transfer-Encoding:binary");
    $objWriter->save('php://output');
}
//  断点打印
function dd($list)
{
    dump($list);die;

}

//对象转数组
function object_array($array) {
    if(is_object($array)) {
        $array = (array)$array;
    } if(is_array($array)) {
        foreach($array as $key=>$value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}
// post请求接口数据
function curl_post($url,$post_data)
{
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
     //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 1);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
     //设置post数据
    $post_data = json_encode($post_data);
     curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
     //执行命令
     $data = curl_exec($curl);
     //关闭URL请求
     curl_close($curl);

     return $data;
}

function curl_get($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


/**
 * @param array $array 要整理的数组
 * @param string $key 要取出的键值
 * @return string      取出的值拼接成的字符串
 * @time: 2019/6/18
 * @autor: duheyuan
 * 键值拼接 数组转字符串
 */
function idsArrayToStr($array = [] , $key = 'id')
{
    $ids = '';
    foreach ($array as $k=>$v){
        $ids .= $v[$key] . ',';
    }
    $ids = rtrim($ids,',');

    return $ids;

}

function array_sort($arr){
    if(empty($arr)) return $arr;
    foreach($arr as $k => $a){
        if(!is_array($a)){
            arsort($arr); // could be any kind of sort
            return $arr;
        }else{
            $arr[$k] = array_sort($a);
        }
    }
    return $arr;
}

/**
 * 二维数组根据某个字段排序
 * @param array $array 要排序的数组
 * @param string $keys 要排序的键字段
 * @param int $sort 排序类型  SORT_ASC     SORT_DESC
 * @return array 排序后的数组
 */
function arraySort($array, $keys, $sort = SORT_DESC) {
    $keysValue = [];
    foreach ($array as $k => $v) {
        $keysValue[$k] = $v[$keys];
    }
    array_multisort($keysValue, $sort, $array);
    return $array;
}


//  返回成功数据
function responseSuccess($data = [],$status = 1001,$msg = '成功')
{
    $result = [
        'status' => $status,
        'msg' => $msg,
        'data' => $data
    ];
    return(json($result));
}

//  返回错误数据
function responseError($data= [], $status = 2001,$msg = '错误')
{
    $result = [
        'status' => $status,
        'msg' => $msg,
        'data' => $data
    ];
    return(json($result));
}


function FromXml($xml)
{
    if (!$xml) {
        echo "xml数据异常！";
    }
    //将XML转为array
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $data;
}

// 将xml转化成json
function get_json($data)
{
    $data = iconv("UTF-8", "GBK", $data); // 转成utf-8格式
    $res = json_decode(json_encode(simplexml_load_string($data)),true);
    return $res;
}


function subString_UTF8($str, $start, $lenth)
{
    $len = strlen($str);
    $r = array();
    $n = 0;
    $m = 0;
    for ($i = 0; $i < $len; $i++) {
        $x = substr($str, $i, 1);
        $a = base_convert(ord($x), 10, 2);
        $a = substr('00000000' . $a, -8);
        if ($n < $start) {
            if (substr($a, 0, 1) == 0) {
            } elseif (substr($a, 0, 3) == 110) {
                $i += 1;
            } elseif (substr($a, 0, 4) == 1110) {
                $i += 2;
            }
            $n++;
        } else {
            if (substr($a, 0, 1) == 0) {
                $r[] = substr($str, $i, 1);
            } elseif (substr($a, 0, 3) == 110) {
                $r[] = substr($str, $i, 2);
                $i += 1;
            } elseif (substr($a, 0, 4) == 1110) {
                $r[] = substr($str, $i, 3);
                $i += 2;
            } else {
                $r[] = '';
            }
            if (++$m >= $lenth) {
                break;
            }
        }
    }
    return join($r);
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


/**
 * 生成宣传海报
 * @param  array 	参数,包括图片和文字
 * @param  string 	$filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
 * @return [type] [description]
 */
function createPoster($config=array(),$filename=""){
    //如果要看报什么错，可以先注释调这个header
    if(empty($filename)) header("content-type: image/png");

    $imageDefault = array(
        'left'=>0,
        'top'=>0,
        'right'=>0,
        'bottom'=>0,
        'width'=>100,
        'height'=>100,
        'opacity'=>100
    );
    $textDefault =  array(
        'text'=>'',
        'left'=>0,
        'top'=>0,
        'fontSize'=>32,             //字号
        'fontColor'=>'255,255,255', //字体颜色
        'angle'=>0,
    );

    $background = $config['background'];//海报最底层得背景
    //背景方法
    $backgroundInfo = getimagesize($background);
    $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
    $background = $backgroundFun($background);

    $backgroundWidth = imagesx($background);    //背景宽度
    $backgroundHeight = imagesy($background);   //背景高度

    $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
    $color = imagecolorallocate($imageRes, 0, 0, 0);
    imagefill($imageRes, 0, 0, $color);

    // imageColorTransparent($imageRes, $color);    //颜色透明

    imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));

    //处理了图片
    if(!empty($config['image'])){
        foreach ($config['image'] as $key => $val) {
            $val = array_merge($imageDefault,$val);

            $info = getimagesize($val['url']);
            $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
            if($val['stream']){		//如果传的是字符串图像流
                $info = getimagesizefromstring($val['url']);
                $function = 'imagecreatefromstring';
            }
            if ($val['is_yuan']){
                
            }
            $res = $function($val['url']);
            $resWidth = $info[0];
            $resHeight = $info[1];
            //建立画板 ，缩放图片至指定尺寸
            $canvas=imagecreatetruecolor($val['width'], $val['height']);
            imagefill($canvas, 0, 0, $color);
            //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
            imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
            $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
            //放置图像
            imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
        }
    }

    //处理文字
    if(!empty($config['text'])){
        foreach ($config['text'] as $key => $val) {
            $val = array_merge($textDefault,$val);
            list($R,$G,$B) = explode(',', $val['fontColor']);
            $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
            $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
            imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],$val['text']);
        }
    }

    //生成图片
    if(!empty($filename)){
        $res = imagejpeg ($imageRes,$filename,90); //保存到本地
        imagedestroy($imageRes);
        if(!$res) return false;
        return $filename;
    }else{
        imagejpeg ($imageRes);			//在浏览器上显示
        imagedestroy($imageRes);
    }
}

function nonce_str()
{
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $str = "";
    for ($i = 0; $i < 32; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}