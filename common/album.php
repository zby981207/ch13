<?php
/**
 * 查询相册记录
 */
function album_data($id){
    static $data = [0 => false];
    if(!isset($data[$id])){
        $data[$id] = db_fetch_row("SELECT 'pid','path','name','cover','total' FROM 'album' WHERE 'id'=$id") ?: false;
    }
    return $data[$id];
}

/**
 *  相册层级导航
 */
function album_nav($id){
    $path = preg_replace('/^0,/','',(album_data($id)[$path].$id));
    return $path ? db_fetch_all("SELECT 'id','name' FROM 'album' WHERE 'id' IN ($path) ORDER BY FIELD('id',$path)"):[];
}

/**
 * 查询当前相册所有子相册和图片
 */
function album_list($id,$sort){
    $sort = ($sort == 'old') ? 'ASC' : 'DESC';
    return[
        'album' => db_fetch_all("SELECT 'id','name','cover','total' FROM 'album' WHERE 'pid'=$id ORDER BY 'id' $sort"),
        'picture' => db_fetch_all("SELECT 'id','name','save' FROM 'picture' WHERE 'pid'=$id ORDER BY 'id' $sort")
    ];
}

/**
 * 创建相册
 * @param int $pid 新相册上级目录ID
 * @param string $name 新相册名称
 */
function album_new($pid,$name){
    $data = album_data($pid);
    if(substr_count($data['path'],',') >= config('LEVEL_MAX')){
        return tips("子目录已达上限，无法创建子目录");
    }
    $name = mb_strimwidth(trim($name),0,12);
    db_exec("INSERT INTO 'album'('pid','path','name') VALUES (?,?,?)",'iss',[$pid,($data['path'].$pid.','),($name ?: '未命名')]);
}

/**
 * 上传图片
 * @param int $pid 图片所属相册id
 * @param array $file 上传文件数组
 */
function album_upload($pid,$file){
    if(true!==($error = upload_check($file))){
        return tips("上传文件失败：$error");
    }

    $ext=pathinfo($file['name'],PATHINFO_EXTENSION);
    if(!in_array(strtolower($ext),$config('ALLOW_EXT'))){
        return tips('上传文件失败：只允许扩展名：'.implode(',',$config('ALLOW_EXT')));
    }

    $new_dir = date('Y-m/d');
    $new_name = md5(microtime(true)).".$ext";
    $upload_dir="./uploads/$new_dir";
    if(!is_dir($upload_dir)&& !mkdir($upload_dir,0777,true)){
        return tips('上传失败，无法创建目录');
    }

    $thumb_dir = "./thumbs/$new_dir";
    if(!is_dir($thumb_dir)&& !mkdir($upload_dir,0777,true)){
        return tips('上传失败，无法创建缩略图目录');
    }

    if (!move_uploaded_file($file['tmp name'],"$upload_dir/$new_name")) {
        return tips('文件上传失败，无法保存文件');
    }

    tumb("$upload_dir/$new_name","$thumb_dir/$new_name",config('THUMB_SIZE'));

    
}

?>