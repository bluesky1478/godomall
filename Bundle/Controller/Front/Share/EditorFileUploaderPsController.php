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
namespace Bundle\Controller\Front\Share;

use Framework\Debug\Exception\AlertBackException;
use Request;
use Component\Storage\Storage;

class EditorFileUploaderPsController extends \Controller\Front\Controller
{
    public function index()
    {
        // default redirection
        $url = Request::request()->get("callback") . '?callback_func=' . Request::request()->get("callback_func");
        $bSuccessUpload = is_uploaded_file(Request::files()->get('Filedata.tmp_name'));

        // SUCCESSFUL
        if ($bSuccessUpload == true) {
            try {
                $storage = Storage::disk(Storage::PATH_CODE_EDITOR, 'http');  //Check HTTP Class
                if (method_exists($storage, 'setHttpType') === true && getenv('GODO_DISTRIBUTED_TYPE') !== 'origin') {
                        $result = $storage->setHttpType('multipart')
                        ->setHttpOptions(['uploadName' => 'Filedata', 'target' => 'editor', 'url' => $url, 'methodName' => 'saveEditorFile'])
                        ->upload();
                    $url = $result['data'];
                } else {
                    $url = self::saveEditorFile($url);
                }
            } catch (\Exception $e) {
                throw new AlertBackException($e->getMessage());
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

    /**
     * saveEditorFile
     *
     * @param $url
     *
     * @return string
     */
    public static function saveEditorFile($url, $httpStorageFl = 'n') {
        $tmp_name = Request::files()->get('Filedata.tmp_name');
        $ext = strtolower(pathinfo(Request::files()->get('Filedata.name'), PATHINFO_EXTENSION));
        $fileName = pathinfo(Request::files()->get('Filedata.name'), PATHINFO_FILENAME);
        if(preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $fileName)){   //한글이면
            $fileName = md5($fileName);
        }
        $saveFileName = $fileName.'_'.date('His').'.'.$ext;
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

            $uploadDir = Request::server()->get('DOCUMENT_ROOT') . $uploadCode;

            if (!is_dir($uploadDir)) {
                $old = umask(0);
                mkdir($uploadDir, 0755, true);
                umask($old);
            }

            $newPath = $uploadDir . urlencode($name);
            move_uploaded_file($tmp_name, $newPath);
            @chmod($newPath,0707);

            /* 분산 서버에 접속되었을 경우 원본 서버에 이미지가 저장되므로 원본 서버 호출하고 실제 저장 시, 도메인은 제거하여 저장함 */
            $tmpFileUrl = $uploadCode . urlencode(urlencode($name));
            if ($httpStorageFl === 'y') {
                $imageApiDomain = getenv('GODO_DEFAULT_DOMAIN');
                if (empty($domain) === true) {
                    $imageApiDomain = Request::getDefaultHost();
                }
                $tmpFileUrl = 'http://api.' . $imageApiDomain . $tmpFileUrl;
            }
            $url .= "&bNewLine=true";
            $url .= "&sFileName=" . urlencode(urlencode($name));
            $url .= "&sFileURL=" . $tmpFileUrl;
        }
        return $url;
    }
}