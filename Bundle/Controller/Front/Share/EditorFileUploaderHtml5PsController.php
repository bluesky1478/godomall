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

use Component\Storage\Storage;
use Request;

class EditorFileUploaderHtml5PsController extends \Controller\Front\Controller
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
        $saveFileName = $fileName.'_'.date('His').'.'.$ext;
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
            try {
                $storage = Storage::disk(Storage::PATH_CODE_EDITOR, 'http'); //Check HTTP Class
                if (method_exists($storage, 'setHttpType') === true && getenv('GODO_DISTRIBUTED_TYPE') !== 'origin') {
                    $fileInfo = $storage->setHttpType('multipart')->setHttpOptions(['uploadName' => 'uploadFile', 'target' => 'editor', 'newPath' => $newPath, 'uploadDir' => $uploadDir, 'methodName' => 'html5'])->setHttpOptions(['name' => 'uploadFile', 'content' => $file->content, 'filename' => $file->name], true)->upload();
                    $sFileInfo = $fileInfo['data'];
                } else { //HTTP Storage를 사용할 수 없을 경우 레거시 보장을 위해 기존 로직 실행.
                    if (file_put_contents($newPath, $file->content)) {
                        @chmod($newPath,0707);
                        $sFileInfo .= "&bNewLine=true";
                        $sFileInfo .= "&sFileName=" . $file->name;
                        $sFileInfo .= "&sFileURL=" . Storage::disk(Storage::PATH_CODE_EDITOR,'local')->getHttpPath($uploadDir) . $file->name;
                    }
                }
            } catch (\Exception $e) {
                exit("ERRORMSG_" . $e->getMessage());
            }
            echo $sFileInfo;
        }
        exit;
    }
}
