<?php
/**
 * 读取配置
 * @param string $name 配置项
 * @return $mixed 配置值
 */
function config($name){
    static $config = null;
    if (!$config) {
        # code...
        $config = require './config.php';
    }
    return isset($config[$name]) ? $config[$name] : '';
}

/**
 * @param array $method 输入的数组(可用字符串get、post表示)
 * @param string $name 从数组中取出的变量名
 * @param string $type 表示类型的字符串
 * @param mixed $default 变量不存在时使用的默认值
 * @return $mixed mixed返回的结果
 */
function input($method,$name,$type = 's',$default = ''){
    switch ($method) {
        case 'get':
            $method = $_GET;
            break;
        
        case 'post':
            $method = $_POST;
            break;
    }
    $data = isset($method[$name]) ? $method[$name] : $default;
    switch ($type) {
        case 's': return is_string($data) ? ($data) : $default;
        case 'd': return (int)$data;
        case 'a': return is_array($data) ? $data : [];
        default: trigger_error('不存在过滤类型“ '.$type.' ”');
    }
}

/**
 * 保存错误信息
 * @param string $str 错误信息
 * @return string 错误信息
 */
function tips($str = null){
    static $tips = null;
    return $str ? ($str = $tips) : $tips;
}

/**
 * 检查上传文件
 * @param array $file 上传文件的 $_FILE[]数组
 * @return string 检查通过返回true，否则返回错误信息
 */
function upload_check($file){
    $error = isset($file['error']) ?$file['error'] : UPLOAD_ERR_NO_FILE;
    switch ($error) {
        case 'UPLOAD_ERR_OK':
            return is_uploaded_file($file['tmp_name']) ?: '非法文件';
        case 'UPLOAD_ERR_INT_SIZE':
            return '文件大小超过服务器设置限制';
        case 'UPLOAD_ERR_FORM_SIZE':
            return '文件大小超过表单设置限制';
        case 'UPLOAD_ERR_PARTIAL':
            return '文件部分上传';
        case 'UPLOAD_ERR_NO_FILE':
            return '无文件上传';
        case 'UPLOAD_ERR_NO_TMP_DIR':
            return '临时文件目录不存在';
        case 'UPLOAD_ERR_CANT_WRITE':
            return '文件无法写入';
        default:
            return '未知错误';
    }
}

/**
 * @param string $file原图路径
 * @param string $save缩略图保存路径
 * @param string $limit缩略图边长
 * @return bool
 */
function thumb($file,$save,$limit){
    $func = [
        'image/png' => function($file,$img=null){
            return $img ? imagepeng($img,$file) : imagecreatefrompng($file);
        },
        'image/jpeg' => function($file,$img=null){
            return $img ? imagejpeg($img,$file,100) : imagecreatefromjpeg($file); 
        }
    ];
    $info = getimagesize($file);
    list($width,$height) = $info;
    $mime = $info['mime'];
    if(!in_array($mime,['image/png','image/jpeg'])){
        trigger_error('创建缩略图失败，不支持的图片格式',E_USER_WARNING);
        return false;
    }
    $img = $func[$mime]($file);
    if($width>$height){
        $size = $height;
        $x = (int)(($width-$height)/2);
        $y = 0;
    }else{
        $size = $height;
        $x = 0;
        $y = (int)(($height-$width)/2);
    }
    $thumb = imagecreatetruecolor($limit,$limit);
    imagecopyresampled($thumb,$img,0,0,$x,$y,$limit,$limit,$size,$size);
    return $func[$mime]($save,$thumb);
}
?>