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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use Framework\Debug\Exception\HttpException;
use Framework\Utility\FileUtils;
use Framework\Utility\GodoUtils;
use Request;
use Exception;
use FileHandler;
use App;

/**
 * HTML 에디터 처리
 *
 * @package Bundle\Controller\Admin\Share
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class PopupWebftpPsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // webftp 정의
            $file = Request::request()->get('file') ? USERPATH . Request::request()->get('file') : USERPATH . 'data';
            if(getenv('setting') == 'y'){
                if(Request::request()->get('file')){
                    $file = str_replace('../../user/',USERPATH,Request::request()->get('file'));
                }

                if(Request::request()->get('target')){
                    $target = str_replace('../../user/',USERPATH,Request::request()->get('target'));
                    Request::request()->set('target',$target);
                }

//                debug(Request::request()->get('target'),true);
            }
            switch (Request::request()->get('mode')) {

                // 삭제하기
                case 'delete':
                    if(empty(Request::request()->get('file')) === true || in_array($file, [USERPATH . 'data', USERPATH . 'data\\', USERPATH . 'data/']) === true){
                        $this->json(
                            [
                                'error'   => 1,
                                'message' => __('삭제할 파일이 없습니다.'),
                            ]
                        );
                    }
                    FileHandler::delete(iconv('UTF-8', 'CP949', $file), true);
                    $this->json(
                        [
                            'error' => 0,
                            'file'  => $file,
                        ]
                    );
                    break;

                // 폴더만들기
                case 'mkdir':
                    $dir = Request::post()->get('name');
                    $dir = str_replace('/', '', $dir);
                    if (substr($dir, 0, 2) === '..') {
                        throw new \RuntimeException(__('최상위 디렉토리에 폴더를 생성하실 수 없습니다.'));
                    }
                    chdir($file);
                    FileHandler::makeDirectory(iconv('UTF-8', 'CP949', Request::post()->get('name')));
                    $this->json(
                        [
                            'error' => 0,
                            'name'  => Request::post()->get('name'),
                        ]
                    );
                    break;

                // 이름 변경하기
                case 'rename':
                    if (!Request::request()->has('target')) {
                        throw new \InvalidArgumentException(__('변경할 이름을 입력해주세요.'));
                    }
                    if(getenv('setting') == 'y'){
                        FileHandler::rename(iconv('UTF-8', 'CP949', $file), iconv('UTF-8', 'CP949',  Request::request()->get('target')));
                    }
                    else {
                        FileHandler::rename(iconv('UTF-8', 'CP949', $file), iconv('UTF-8', 'CP949', USERPATH . Request::request()->get('target')));
                    }

                    $this->json(
                        [
                            'error'  => 0,
                            'target' => Request::post()->get('target'),
                        ]
                    );
                    break;

                // 파일 업로드
                case 'upload':
                    $tmpName = Request::files()->get('file_data.tmp_name');
                    $name = Request::files()->get('file_data.name');
                    $size = Request::files()->get('file_data.size');

                    if (FileUtils::size2Bytes(FileUtils::getMaxUploadSize()) < $size[0]) {
                        throw new \FileException(__('파일 용량을 체크해주세요.'));
                    }
                    if (!FileUtils::isUserDataExtension($name[0])) {
                        throw new \InvalidArgumentException(__('업로드하신 파일 확장자는 지원하지 않습니다.'));
                    }

                    if(Request::post()->get('ow') == 'false' && file_exists(iconv('UTF-8', 'CP949', $file . '/' . $name[0]))){
                        header("HTTP/1.0 500", true, 500);
                        echo "파일명 중복";
                        exit;
                    }

                    // 한글 파일명 업로드를 위해 인코딩 변경 처리
                    move_uploaded_file($tmpName[0], iconv('UTF-8', 'CP949', $file . '/' . $name[0]));
                    break;

                // 다운로드
                case 'download':
                    $filename = basename($file);
                    header('Content-Type: ' . gd_mime_content_type($file));
                    header('Content-Length: ' . filesize($file));
                    header(
                        sprintf(
                            'Content-Disposition: attachment; filename=%s',
                            strpos('MSIE', Request::getReferer()) ? rawurlencode($filename) : "\"$filename\""
                        )
                    );
                    ob_flush();
                    readfile(iconv('UTF-8', 'CP949', $file));
                    break;
            }
            exit;

        } catch (Exception $e) {
            if (Request::isAjax()) {
                $this->json(
                    [
                        'error'   => 1,
                        'message' => $e->getMessage(),
                    ]
                );
            } else {
                debug($e->getMessage());
            }
        }
    }
}
