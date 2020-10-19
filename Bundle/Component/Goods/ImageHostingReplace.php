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

namespace Bundle\Component\Goods;

use Component\Storage\Storage;
use Component\File\StorageHandler;
use Component\Database\DBTableField;
use Framework\Utility\HttpUtils;
use Framework\Utility\SkinUtils;
use Globals;
use FileHandler;
use UserFilePath;
use LogHandler;
use Request;

/**
 * 이미지 호스팅 일괄전환
 * @author jwno
 */
class ImageHostingReplace
{
    // 디비 접속
    protected $db;

    protected $imageHostingReplaceDir = 'gd5replace';

    /**
     * 생성자
     *
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * 넘어온 문자열내의 http로 시작하지않는 이미지경로의 카운트를 리턴
     *
     * @param string $sString 상품의 상세설명
     * @return array 상세설명
     */
    public function getImageReplaceCount($sString)
    {
        $cnt = array('tot' => 0, 'in' => 0);

        if (is_string($sString) === true) {
            $aSplit = $this->_split($sString);
        } else {
            return $cnt;
        }

        $iCnt = count($aSplit);
        for ($i=1; $i < $iCnt; $i += 2) {
            $cnt['tot']++;
            if (preg_match('@^http:\/\/@ix', $aSplit[$i]));
            else {
                if (substr($aSplit[$i],0,1) == '/') $imgPath = Request::server()->get('DOCUMENT_ROOT') . $aSplit[$i];
                if (file_exists($imgPath) === true) {
                    $chkimg = getimagesize($imgPath);
                    if ($chkimg[2] != 0){
                        $cnt['in']++;
                    }
                }
            }
        }

        return $cnt;
    }

    private function _split($sString)
    {
        $Ext = '(?<=src\=")(?:[^"])*[^"](?=")'.
            "|(?<=src\=')(?:[^'])*[^'](?=')".
            '|(?<=src\=\\\\")(?:[^"])*[^"](?=\\\\")'.
            "|(?<=src\=\\\\')(?:[^'])*[^'](?=\\\\')";
        $sPattern = '@('. $Ext .')@ix';
        $aSplit = preg_split($sPattern, $sString, -1, PREG_SPLIT_DELIM_CAPTURE);
        return $aSplit;
    }

    /**
     * 단순 ftp 체크
     *
     * @param string $aPost ftp정보
     * @return array 상세설명
     */
    public function checkUseStorage($aPost)
    {
        Storage::checkUseStorage($aPost);
    }

    /**
     * 단순 ftp 체크
     *
     * @param string $aPost ftp정보
     * @return array 상세설명
     */
    public function doReplace($aPost)
    {
        // 이미지 호스팅에 들어갈 폴더명 생성 $this->imageHostingReplaceDir/domain/time()상품번호
        $sno = Globals::get('gLicense.godosno');
        $freedomain_result = HttpUtils::remoteGet('http://gongji.godo.co.kr/userinterface/get.basicdomain.php?sno=' . $sno);
        $sNewDir1 = explode('.', $freedomain_result);
        $oStorage = Storage::customDisk($aPost, true);
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // 처리카운트
        $resultCount = 0;

        foreach ($aPost['goodsNo'] as $val) {
            //$oStorage->createDir('/' . $this->imageHostingReplaceDir . DS . $sNewDir1[0]);
            //$oStorage->createDir('/' . $this->imageHostingReplaceDir . DS . $sNewDir1[0] . DS . $sNewDir2 . $val);
            $tmpData = $goods->getGoodsInfo($val, 'g.goodsNm, g.goodsDescription'); //  기존 상품 정보
            $aSplit = $this->_split($tmpData['goodsDescription']);

            $iCnt = count($aSplit);
            for ($i=1; $i < $iCnt; $i += 2) {
                if (preg_match('@^http:\/\/@ix', $aSplit[$i]));
                else {
                    if (substr($aSplit[$i],0,1) == '/') $imgPath = Request::server()->get('DOCUMENT_ROOT') . $aSplit[$i];
                    if (file_exists($imgPath) === true) {
                        $chkimg = getimagesize($imgPath);
                        if ($chkimg[2] != 0){
                            $orgImage = $aSplit[$i];
                            if (substr($orgImage, 0, 1) != '/') {
                                $orgImage = '/'.$orgImage;
                            }
                            $explodeImage = explode('/', $orgImage);
                            array_pop($explodeImage);
                            $ImageDir = implode('/', $explodeImage);
                            $oStorage->createDir('/' . $this->imageHostingReplaceDir . DS . $sNewDir1[0] . $ImageDir);

                            // 변경된 이미지호스팅 이미지 주소
                            $prevSplit = $aSplit[($i-1)];
                            $prevSplit = substr($prevSplit, -1, 1);
                            if (in_array($prevSplit, array('"', "'")) === false) $prevSplit = '';
                            $hostingImageUrl = 'http://' . $aPost['httpUrl'] . '/' . $this->imageHostingReplaceDir . DS . $sNewDir1[0] . $orgImage;
                            $hostingImageUrl = $hostingImageUrl . $prevSplit . ' godoOld=' . $prevSplit . $aSplit[$i];
                            $tmpData['goodsDescription'] = str_replace($aSplit[$i], $hostingImageUrl, $tmpData['goodsDescription']);

                            if (substr($orgImage, 0, 6) == '/data/') {
                                $orgImage = substr($orgImage, 6);
                            }
                            //파일명이 중복일 경우, skip 될 수 있도록 try 문 추가
                            try{
                                Storage::customCopy(Storage::PATH_CODE_DEFAULT, 'local', $orgImage, $aPost, $this->imageHostingReplaceDir . DS . $sNewDir1[0] . '/data/' . $orgImage);
                            }catch (\Exception $e) {
                            }

                            $loginInfo = "goodsNo : " . $val . chr(10);
                            $loginInfo .= "orgFile : /data/" . $orgImage . chr(10);
                            $loginInfo .= "chgFile : http://" . $aPost['httpUrl'] . '/' . $this->imageHostingReplaceDir . DS . $sNewDir1[0] . '/data/' . $orgImage . chr(10);
                            @error_log($loginInfo, 3, UserFilePath::log('admin', 'image_replace_' . date('Ymd') . '.log'));
                        }
                    }
                }
            }

            $goods->setGoodsDescription($tmpData['goodsDescription'], $val);

            // 상품카운트
            $resultCount++;
        }

        return $resultCount;
    }

}
