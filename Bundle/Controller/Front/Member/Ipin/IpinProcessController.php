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

namespace Bundle\Controller\Front\Member\Ipin;

use Framework\Debug\Exception\AlertCloseException;
use Request;
use Session;

/**
 * Class NiceIpinProcessController
 * NICE신용평가정보 아이핀 모듈 사용자 인증 정보 처리 페이지
 * 원본 파일명 ipin_process.php
 * NICE신용평가정보 아이핀 버전 : VNO-IPIN Service Version 2.0.P(20080929)
 * - 수신받은 데이터(인증결과)를 메인화면으로 되돌려주고, close를 하는 역활을 합니다.
 * @package Controller\Front\Member\Ipin
 * @author  yjwee
 */
class IpinProcessController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /********************************************************************************************************************************************
         * NICE신용평가정보 Copyright(c) KOREA INFOMATION SERVICE INC. ALL RIGHTS RESERVED
         *
         * 서비스명 : 가상주민번호서비스 (IPIN) 서비스
         * 페이지명 : 가상주민번호서비스 (IPIN) 사용자 인증 정보 처리 페이지
         *
         * 수신받은 데이터(인증결과)를 메인화면으로 되돌려주고, close를 하는 역활을 합니다.
         *********************************************************************************************************************************************/

        // 사용자 정보 및 CP 요청번호를 암호화한 데이타입니다. (ipin_main.php 페이지에서 암호화된 데이타와는 다릅니다.)
        $sResponseData = Request::request()->get('enc_data');

        // ipin_main.php 페이지에서 설정한 데이타가 있다면, 아래와 같이 확인가능합니다.
        $sReservedParam1 = Request::request()->get('param_r1');
        $sReservedParam2 = Request::request()->get('param_r2');
        $sReservedParam3 = Request::request()->get('param_r3');

        // 회원가입시 가입경로가 모바일인지 체크, 모바일의 아이핀체크에서 세션에 저장한 값을 불러옴
        $joinGubun = Session::get('joinGubun');

        //////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
        if (preg_match('~[^0-9a-zA-Z+/=]~', $sResponseData, $match)) {
            echo "입력 값 확인이 필요합니다";
            exit;
        }
        if (base64_encode(base64_decode($sResponseData)) != $sResponseData) {
            echo " 입력 값 확인이 필요합니다";
            exit;
        }

        // TODO: 우선 오류나서 주석 처리 필요한지 확인 후 해제할 것
        //        if (preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReservedParam1, $match)) {
        //            echo "문자열1 점검 : " . $match[0];
        //            exit;
        //        }
        //        if (preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReservedParam2, $match)) {
        //            echo "문자열2 점검 : " . $match[0];
        //            exit;
        //        }
        //        if (preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReservedParam3, $match)) {
        //            echo "문자열3 점검 : " . $match[0];
        //            exit;
        //        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////

        if (empty($sResponseData)) {
            throw new AlertCloseException('응답 데이터가 없습니다.');
        }

        $this->setData('joinGubun', $joinGubun);
        $this->setData('sResponseData', $sResponseData);
        $this->setData('sReservedParam1', $sReservedParam1);
        $this->setData('sReservedParam2', $sReservedParam2);
        $this->setData('sReservedParam3', $sReservedParam3);
    }
}
