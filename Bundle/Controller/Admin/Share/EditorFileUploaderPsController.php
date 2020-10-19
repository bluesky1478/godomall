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

use Framework\Debug\Exception\AlertBackException;
use Component\Storage\Storage;
use Request;
use Session;

class EditorFileUploaderPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        // default redirection
        $url = Request::request()->get("callback") . '?callback_func=' . Request::request()->get("callback_func");
        $bSuccessUpload = is_uploaded_file(Request::files()->get('Filedata.tmp_name'));
        $ftpStorage = Request::post()->get('ftpStorage');

        // SUCCESSFUL
        if ($bSuccessUpload == true) {
            $tmp_name = Request::files()->get('Filedata.tmp_name');
            $ext = strtolower(pathinfo(Request::files()->get('Filedata.name'), PATHINFO_EXTENSION));
            $fileName = pathinfo(Request::files()->get('Filedata.name'), PATHINFO_FILENAME);
            if(preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $fileName)){   //한글이면
                $fileName = md5($fileName);
            }
            $saveFileName = preg_replace("/\s+/", "", $fileName).'_'.date('His').'.'.$ext;
            $name = $saveFileName;

            $_tmp = (array)explode('.', $name);
            $filename_ext = strtolower(array_pop($_tmp));
            $allow_file = array("jpg","jpeg","tiff", "png", "bmp", "gif","svg");

            if (!in_array($filename_ext, $allow_file)) {
                $url .= '&errstr=' . $name;
            } else {
                $pathChunk = parse_url(Request::post()->get('editorUrl'));
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
                        $uploadCode = PATH_EDITOR_RELATIVE .'/'. $path . "/" . $bdId . "/";
                    } else {
                        $uploadCode = PATH_EDITOR_RELATIVE .'/'. $path . "/".date('ymd')."/";;
                    }
                } else {
                    $uploadCode = PATH_EDITOR_RELATIVE .'/'. $path . "/".date('ymd')."/";;
                }

                //파일 저장소 설정값 가져오기
                $tmpStorageConf = gd_policy('basic.storage');
                $defaultImageStorage = '';
                $savePath = '';
                // 저장소 디폴트 없을 경우
                if (empty($tmpStorageConf['storageDefault']) === true) {
                    $tmpStorageConf['storageDefault'] = array('imageStorage0' => array('goods'));
                }

                foreach ($tmpStorageConf['storageDefault'] as $index => $item) {
                    if (in_array('goods', $item)) {
                        $defaultImageStorage = $tmpStorageConf['httpUrl'][$index];
                        // 상품등록페이지에서 선택한 저장소와 기본저장소가 다를경우 상품등록페이지에서 선택한 저장소에 파일저장
                        if(empty($ftpStorage) === false) {
                            if ($defaultImageStorage != $ftpStorage) {
                                $defaultImageStorage = $ftpStorage;
                            }
                            if ($ftpStorage == 'url') {
                                if(gd_is_provider() === true){
                                    $scmNo = Session::get('manager.scmNo');
                                    $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
                                    $defaultImageStorage = $scmAdmin->getScm($scmNo)['imageStorage'];
                                } else {
                                    $defaultImageStorage = $tmpStorageConf['httpUrl'][$index];
                                }
                            }
                        }
                    }
                }
                foreach($tmpStorageConf as $key => $value){
                    if($key == 'httpUrl'){
                        foreach($value as $k => $v) {
                            if ($defaultImageStorage == $v){
                                    $savePath = $tmpStorageConf['savePath'][$k];
                            }

                        }
                    }
                }
                // -- 외부저장소가 기본저장소로 설정되어있는 경우 상품 등록 에디터에서 이미지 등록시 외부저장소에 저장
                if($path == 'goods' && $defaultImageStorage != 'local') {
                    $uploadCode = "editor/".date('ymd')."/";
                    $newPath = $uploadCode . urlencode($name);

                    $storage = \App::load('\\Component\\Storage\\Storage');
                    $storageDisk = $storage->disk(Storage::PATH_CODE_GOODS, $defaultImageStorage);
                    $storageDisk->upload($tmp_name, $newPath);
                    $uploadCode = $defaultImageStorage. "/" .$savePath ."/".$path."/". $uploadCode;

                } else {
                    $uploadDir = Request::server()->get('DOCUMENT_ROOT') . $uploadCode;
                    if (!is_dir($uploadDir)) {
                        $old = umask(0);
                        mkdir($uploadDir, 0755, true);
                        umask($old);
                    }
                    $newPath = $uploadDir . urlencode($name);
                    move_uploaded_file($tmp_name, $newPath);
                    @chmod($newPath, 0707);
                }

                $url .= "&bNewLine=true";
                $url .= "&sFileName=" . urlencode(urlencode($name));
                $url .= "&sFileURL=" . $uploadCode . urlencode(urlencode($name));
            }
        } // FAILED
        else {
            $errorCode = Request::files()->get('Filedata.error');
            switch ($errorCode) {
                case UPLOAD_ERR_INI_SIZE :
                    $errorMsg = sprintf(__('업로드 용량이 %1$s MByte(s) 를 초과했습니다.'), MAX_UPLOAD_SIZE);
                    break;
                default :
                    $errorMsg = __('알수 없는 오류입니다.') . '( ERROR CODE : ' . $errorCode . ')';
            }
            throw new AlertBackException($errorMsg);
        }

        header('Location: ' . $url);
        exit();
    }
}
