<?php
namespace Bundle\Component\Storage;

use Framework\File\FileInfo;
use Framework\Http\Response;
use Framework\StaticProxy\Proxy\UserFilePath;
use Framework\Utility\ComponentUtils;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use GuzzleHttp\Client;

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
abstract class AbstractStorage extends Filesystem implements \Component\Storage\StorageInterface
{
    protected $basePath;    //기본 경로 ex) data/board/
    protected $httpUrl;
    protected $storageName; //저장소 이름 ex) local. http://qnibus.com
    protected $ftpData;

    public function getDiskPath($pathCode)
    {
        $path = null;
        switch ($pathCode) {
            case Storage::PATH_CODE_GOODS:
                $path = $this->getPath('goods');
                break;
            case Storage::PATH_CODE_ADD_GOODS:
                $path = $this->getPath('add_goods');
                break;
            case Storage::PATH_CODE_GIFT:
                $path = $this->getPath('gift');
                break;
            case Storage::PATH_CODE_BOARD:
                $path = $this->getPath('board');
                break;
            case Storage::PATH_CODE_EDITOR:
                $path = $this->getPath('editor');
                break;
            case Storage::PATH_CODE_ETC:
                $path = $this->getPath('etc');
                break;
            case Storage::PATH_CODE_CATEGORY:
                $path = $this->getPath('category');
                break;
            case Storage::PATH_CODE_BRAND:
                $path = $this->getPath('brand');
                break;
            case Storage::PATH_CODE_DISPLAY:
                $path = $this->getPath('display');
                break;
            case Storage::PATH_CODE_MOBILE:
                $path = $this->getPath('mobile');
                break;
            case Storage::PATH_CODE_COUPON_IMAGE:
                $path = $this->getPath('coupon_image');
                break;
            case Storage::PATH_CODE_GOODS_ICON:
                $path = UserFilePath::icon('goods_icon');
                break;
            case Storage::PATH_CODE_COUPON_BG:
                $path = UserFilePath::icon('coupon_bg');
                break;
            case Storage::PATH_CODE_GROUP_ICON:
                $path = UserFilePath::icon('group_icon');
                break;
            case Storage::PATH_CODE_GROUP_IMAGE:
                $path = UserFilePath::icon('group_image');
                break;
            case Storage::PATH_CODE_FRONT_SKIN_CODI:
                // 배경이미지가 저장되는 경로
                $path = UserFilePath::frontSkin(\Globals::get('gSkin.frontSkinWork'), 'img', 'codi');
                break;
            case Storage::PATH_CODE_MOBILE_SKIN_CODI:
                // 배경이미지가 저장되는 경로
                $path = UserFilePath::mobileSkin(\Globals::get('gSkin.mobileSkinWork'), 'img', 'codi');
                break;
            case Storage::PATH_CODE_SCM:
                // 공급사 관리 - 공급사 대표이미지, 공급사 사업자 등록증 이미지
                $path = UserFilePath::data('scm');
                break;
            case Storage::PATH_CODE_CHECK:
                // 공급사 관리 - 공급사 대표이미지, 공급사 사업자 등록증 이미지
                $path = UserFilePath::data('check');
                break;
            case Storage::PATH_CODE_COMMON:
                // 프론트 - 공통 파일
                $path = UserFilePath::data('common');
                break;
            case Storage::PATH_CODE_ATTENDANCE_ICON_USER:
                $path = UserFilePath::data('attendance', 'upload');
                break;
            case Storage::PATH_CODE_GHOST_DEPOSITOR_BANNER:
                $path = UserFilePath::data('ghost_depositor', 'banner');
                break;
            case Storage::PATH_CODE_MULTI_POPUP:
                $path = UserFilePath::data('multi_popup');
                break;
            case Storage::PATH_CODE_POLL:
                $path = UserFilePath::data('poll');
                break;
            case Storage::PATH_CODE_COMMONIMG:
                $path = UserFilePath::data('commonimg');
                break;
            case Storage::PATH_CODE_PLUS_REIVEW :
                $path = UserFilePath::data('plus_review');
                break;
            case Storage::PATH_CODE_EVENT_GROUP :
                $path = UserFilePath::data('event_group');
                break;
            case Storage::PATH_CODE_EVENT_GROUP_TMP :
                $path = UserFilePath::data('event_group_tmp');
                break;
            case Storage::PATH_CODE_DEFAULT:
                $path = UserFilePath::data();
                break;
            case Storage::PATH_CODE_JOIN_EVENT:
                $path = UserFilePath::data('join_event');
                break;
            case '' :
                $path = '';
                break;
            default :
                throw new \Exception('NOT EXISTS PATH_CODE');
                break;
        }
        return $path;
    }

    /**
     * upload
     * 일반 파일업로드
     * @param $filePath
     * @param $savePath
     * @param array $option 섬네일 이미지 생성 시 width 값 0이상
     * @return 저장된 파일경로
     * @throws \Exception
     */
    public function upload($filePath, $savePath, $option = ['width' => 0, 'height' => 0, 'quality' => 'high', 'overWrite' => true])
    {
        \Logger::debug(__METHOD__, func_get_args());
        $this->setAdapter();
        $option['overWrite'] = (!$option['overWrite']) ? true : $option['overWrite'];
        $option['quality'] = (!$option['quality']) ? 'high' : $option['quality'];

        if ($option['quality'] == 'high') {
            $quality = 100;
        } else if ($option['quality'] == 'low') {
            $quality = 80;
        } else {
            $quality = 80;
        }
        if ($option['width'] > 0) {
            $tmpfname = tempnam("tmp", "board");
            copy($filePath, $tmpfname);
            $filePath = $tmpfname;
            $this->_convertThumbnail($filePath, $option['width'], $quality, $option['height']);
        }

        $stream = fopen($filePath, 'r+');
        if ($this->isFileExists($savePath)) {
            if ($option['overWrite'] == false) {
                throw new \Exception(__('중복된 파일이 존재합니다.'));
            }
            $return = $this->updateStream($this->getMountPath($savePath), $stream);
        } else {
            $return = $this->writeStream($this->getMountPath($savePath), $stream, ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);
        }
        if ($tmpfname) {
            @unlink($tmpfname);
        }
        fclose($stream);
        if ($return) {
            return $this->getHttpPath($savePath);
        }

        return false;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    //@todo : 이미지 관련 유틸로 대체
    protected function _convertThumbnail($path, $width, $quality = 100, $height = null)
    {
        list($imageWidth, $imageHeight, $imageType) = @getimagesize($path);

        switch ($imageType) {
            case 1:
                $image = imagecreatefromgif($path);
                break;
            case 2:
                $image = imagecreatefromjpeg($path);
                break;
            case 3:
                $image = imagecreatefrompng($path);
                break;
            default:
                return;
        }

        if ($width) {
            if ($imageWidth > $width) {
                $ratio = $imageWidth / $width;
            } else {
                $ratio = 1;
            }

            if ($width > 0 && $height > 0) {
                $saveImageSize['width'] = $width;
                $saveImageSize['height'] = $height;
            } else {
                $saveImageSize['width'] = round($imageWidth / $ratio);
                $saveImageSize['height'] = round($imageHeight / $ratio);
            }
            $dest = imagecreatetruecolor($saveImageSize['width'], $saveImageSize['height']);

            //png인 경우 백그라운드를 하얀색으로
            if ($imageType == 3) {
                imagealphablending($dest, false);
                imagesavealpha($dest, true);
                $transparentindex = imagecolorallocatealpha($dest, 255, 255, 255, 127);
                imagefill($dest, 0, 0, $transparentindex);
            }
            imagecopyresampled($dest, $image, 0, 0, 0, 0, $saveImageSize['width'], $saveImageSize['height'], $imageWidth, $imageHeight);

            switch ($imageType) {
                case 1:
                    imagegif($dest, $path);
                    break;
                case 2:
                    imagejpeg($dest, $path, $quality);
                    break;
                case 3:
                    imagepng($dest, $path);
                    break;
                default:
                    throw new \Exception(__('썸네일저장 가능한 타입이 아닙니다.'));
            }

        }
    }

    private function isIE()
    {
        $userAgent = \Request::getUserAgent();
        preg_match('/MSIE (.*?);/', $userAgent, $matches);
        if (count($matches) < 2) {
            preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $userAgent, $matches);
        }
        if (count($matches) > 1) {  //$matches변수값이 있으면 IE브라우저
            return true;
        }

        return false;
    }

    /**
     * 파일 다운로드
     *
     * @param $realFilePath
     * @param $downloadFilename
     * @throws \Exception
     */
    protected function setDownloadHeader($realFilePath, $downloadFilename)
    {
        if ($this->isIE()) {
            $downloadFilename = iconv("UTF-8", "cp949//IGNORE", $downloadFilename);
        }

        $fileInfo = new FileInfo($realFilePath);
        if (!$fileInfo->isFile()) {
            throw new \Exception(__('다운로드 하실 파일경로가 잘못되었습니다.'));
        }
        $mimeType = $fileInfo->getMimeType() != null ?: 'file/unknown';
        $response = new Response();

        $response->prepare(\App::getInstance('request'));
        $response->getHeaders()->set('Content-type', $mimeType);
        $response->getHeaders()->set('Content-Disposition', sprintf('attachment; filename="%s"', $downloadFilename));
        $response->getHeaders()->set('Content-Length', filesize($realFilePath));
        $response->getHeaders()->set('Content-Transfer-Encoding', 'binary');
        $response->sendHeader();
        $response->setContent(readfile($realFilePath));
        $response->send();
    }

    protected function setHttpClient($config) {
        $this->adapter = new Client();
        $this->setConfig($config);
    }
}
