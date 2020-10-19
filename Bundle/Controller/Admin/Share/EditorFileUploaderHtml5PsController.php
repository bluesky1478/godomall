<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Share;

use Component\Storage\Storage;
use Request;
use Session;

class EditorFileUploaderHtml5PsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $sFileInfo = '';
        $headers = array();

        foreach (Request::server()->toArray() as $k => $v) {
            if (substr($k, 0, 9) == "HTTP_FILE") {
                $k = substr(strtolower($k), 5);
                $headers[$k] = $v;
            }
        }
        $file = new \stdClass();
        $ext = strtolower(pathinfo($headers['file_name'], PATHINFO_EXTENSION));
        $fileName = urldecode(pathinfo($headers['file_name'], PATHINFO_FILENAME));
        if(preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $fileName)){   //한글이면
            $fileName = md5($fileName);
        }
        $saveFileName = preg_replace("/\s+/", "", $fileName).'_'.date('His').'.'.$ext;
        $file->name = $saveFileName;
        $file->size = $headers['file_size'];
        if ($file->size > (MAX_UPLOAD_SIZE * 1024 * 1024)) {
            exit("ERRORMSG_" . ' `'.$headers['file_name'].'`파일의 업로드 용량이 ' . MAX_UPLOAD_SIZE . 'MByte(s) 를 초과했습니다.');
        }
        $file->content = file_get_contents("php://input");

        $_tmp = (array)explode('.', $file->name);
        $filename_ext = strtolower(array_pop($_tmp));
        $allow_file = array("jpg","jpeg","tiff", "png", "bmp", "gif","svg");

        if (!in_array($filename_ext, $allow_file)) {
            echo "NOTALLOW_" . $file->name;
        } else {
            $pathChunk = parse_url(Request::get()->get('editorUrl'));
            $pathArray = explode('/', $pathChunk['path']);
            $queryChunk = $pathChunk['query'];
            parse_str($queryChunk);
            array_shift($pathArray);
            array_pop($pathArray);
            $path = $pathArray[count($pathArray) - 1];
            if ($path == 'board') {
                if(Request::get()->has('bdId')) {
                    $bdId = Request::get()->get('bdId');
                }
                if (isset($bdId)) {
                    $uploadDir =  $path . "/" . $bdId . "/";
                } else {
                    $uploadDir = $path . "/".date('ymd')."/";
                }
            } else {
                $uploadDir =  $path . "/".date('ymd')."/";
            }

            Storage::disk(Storage::PATH_CODE_EDITOR,'local')->createDir($uploadDir);
            $newPath = Storage::disk(Storage::PATH_CODE_EDITOR,'local')->getRealPath($uploadDir) . iconv("utf-8", "cp949", $file->name);

            if (file_put_contents($newPath, $file->content)) {
                @chmod($newPath,0707);
                $sFileInfo .= "&bNewLine=true";
                $sFileInfo .= "&sFileName=" . $file->name;
                $sFileInfo .= "&sFileURL=" . Storage::disk(Storage::PATH_CODE_EDITOR,'local')->getHttpPath($uploadDir) . $file->name;
            }

            // -- 외부저장소가 기본저장소로 설정되어있는 경우 상품 등록 에디터에서 이미지 등록시 외부저장소에 저장
            $ftpStorage = Request::get()->get('ftpStorage');
            if($path == 'goods'){
                $tmpStorageConf = gd_policy('basic.storage');
                $defaultImageStorage = '';
                if (empty($tmpStorageConf['storageDefault']) === true) {
                    $tmpStorageConf['storageDefault'] = array('imageStorage0' => array('goods'));
                }
                foreach ($tmpStorageConf['storageDefault'] as $index => $item) {
                    if (in_array('goods', $item)) {
                        $defaultImageStorage = $tmpStorageConf['httpUrl'][$index];
                        // 상품등록페이지에서 선택한 저장소와 기본저장소가 다를경우 상품등록페이지에서 선택한 저장소에 파일저장
                        if(empty($ftpStorage)===false && $defaultImageStorage != $ftpStorage) {
                            $defaultImageStorage = $ftpStorage;
                        }
                        if($ftpStorage == 'url'){
                            if(gd_is_provider() === true){
                                $scmNo = Session::get('manager.scmNo');
                                $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
                                $defaultImageStorage = $scmAdmin->getScm($scmNo)['imageStorage'];
                            }else {
                                $defaultImageStorage = $tmpStorageConf['httpUrl'][$index];
                            }
                        }
                    }
                }
                foreach($tmpStorageConf as $key => $value){
                    if($key == 'httpUrl'){
                        foreach($value as $k => $v) {
                            if ($defaultImageStorage == $v){
                                    $savePath = $defaultImageStorage.'/'.$tmpStorageConf['savePath'][$k];
                            }

                        }
                    }
                }
                if ($defaultImageStorage != 'local') {
                    $uploadCode = "editor/".date('ymd')."/";
                    $externalPath = $uploadCode . urlencode($file->name);

                    // 외부저장소 이미지 저장
                    Storage::disk(Storage::PATH_CODE_GOODS, $defaultImageStorage)->upload($newPath, $externalPath);

                    // 로컬에 저장된 이미지 삭제
                    $deletePath = "/goods/".date('ymd')."/".$file->name;
                    Storage::disk(Storage::PATH_CODE_EDITOR,'local')->delete($deletePath);

                    $sFileInfo .= "&sFileURL=" . $savePath . '/' . $path .'/' .$uploadCode .$file->name;
                }
            }
            echo $sFileInfo;
        }
        exit;
    }
}