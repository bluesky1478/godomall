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

namespace Bundle\Controller\Mobile\Member\Ipin;

use Logger;
use Request;
use Session;

/**
 * Class NiceIpinResultController
 * NICE신용평가정보 아이핀 모듈 사용자 인증 정보 결과 페이지
 * 원본 파일명 ipin_result.php
 * NICE신용평가정보 아이핀 버전 : VNO-IPIN Service Version 2.0.P(20080929)
 * @package Controller\Mobile\Member\Ipin
 * @author  yjwee
 */
class IpinApplyController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /********************************************************************************************************************************************
         * NICE신용평가정보 Copyright(c) KOREA INFOMATION SERVICE INC. ALL RIGHTS RESERVED
         *
         * 서비스명 : 가상주민번호서비스 (IPIN) 서비스
         * 페이지명 : 가상주민번호서비스 (IPIN) 사용자 인증 정보 결과 페이지
         *
         * 수신받은 데이터(인증결과)를 복호화하여 사용자 정보를 확인합니다.
         *********************************************************************************************************************************************/

        $ipin = gd_policy('member.ipin');
        $sSiteCode = $ipin['siteCode'];            // IPIN 서비스 사이트 코드		(NICE신용평가정보에서 발급한 사이트코드)
        $sSitePw = $ipin['sitePass'];            // IPIN 서비스 사이트 패스워드	(NICE신용평가정보에서 발급한 사이트패스워드)

        $sEncData = "";            // 암호화 된 사용자 인증 정보
        $sDecData = "";            // 복호화 된 사용자 인증 정보

        $sRtnMsg = "";            // 처리결과 메세지


        /*
        ┌ sType 변수에 대한 설명  ─────────────────────────────────────────────────────
            데이타를 추출하기 위한 구분값.

            SEQ : 요청번호 생성
            REQ : 요청 데이타 암호화
            RES : 요청 데이타 복호화
        └────────────────────────────────────────────────────────────────────
        */
        $sType = "RES";


        /*
        ┌ sModulePath 변수에 대한 설명  ─────────────────────────────────────────────────────
            모듈 경로설정은, '/절대경로/모듈명' 으로 정의해 주셔야 합니다.

            + FTP 로 모듈 업로드시 전송형태를 'binary' 로 지정해 주시고, 권한은 755 로 설정해 주세요.

            + 절대경로 확인방법
              1. Telnet 또는 SSH 접속 후, cd 명령어를 이용하여 모듈이 존재하는 곳까지 이동합니다.
              2. pwd 명령어을 이용하면 절대경로를 확인하실 수 있습니다.
              3. 확인된 절대경로에 '/모듈명'을 추가로 정의해 주세요.
        └────────────────────────────────────────────────────────────────────
        */
        $serverPhpSelf = Request::getPhpSelf();
        $self_filename = basename($serverPhpSelf);
        $loc = strpos($serverPhpSelf, $self_filename);
        $loc = substr($serverPhpSelf, 0, $loc);
        $sModulePath = str_replace('\\', '/', SYSPATH_IPIN_MODULE . "IPINClient");
        // $sModulePath = $_SERVER['DOCUMENT_ROOT']."/shop/member/ipin/IPINClient";

        // ipin_main.php 에서 저장한 세션 정보를 추출합니다.
        // 데이타 위변조 방지를 위해 확인하기 위함이므로, 필수사항은 아니며 보안을 위한 권고사항입니다.
        $sCPRequest = Session::get('CPREQUEST');

        // 회원가입시 가입경로가 모바일인지 체크, 모바일의 아이핀체크에서 세션에 저장한 값을 불러옴
        $joinGubun = Session::get('joinGubun');


        // ipin_process.php 에서 리턴받은 암호화 된 사용자 인증 정보
        $sEncData = Request::post()->get('enc_data');

        //////////////////////////////////////////////// 문자열 점검///////////////////////////////////////////////
        if (preg_match('~[^0-9a-zA-Z+/=]~', $sEncData, $match)) {
            echo __("입력 값 확인이 필요합니다.");
            exit;
        }
        if (base64_encode(base64_decode($sEncData)) != $sEncData) {
            echo __("입력 값 확인이 필요합니다.");
            exit;
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////

        if ($sEncData != "") {

            // 사용자 정보를 복호화 합니다.
            // 실행방법은 싱글쿼터(`) 외에도, 'exec(), system(), shell_exec()' 등등 귀사 정책에 맞게 처리하시기 바랍니다.
            $sDecData = exec("$sModulePath $sType $sSiteCode $sSitePw $sEncData");
            Logger::info(__METHOD__ . '$sDecData=>' . $sDecData . ', ' . "$sModulePath $sType $sSiteCode $sSitePw $sEncData");

            if ($sDecData == -9) {
                $sRtnMsg = __("입력값 오류 : 복호화 처리시, 필요한 파라미터값의 정보를 정확하게 입력해 주시기 바랍니다.");
            } else if ($sDecData == -12) {
                $sRtnMsg = __("NICE신용평가정보에서 발급한 개발정보가 정확한지 확인해 보세요.");
            } else {

                // 복호화된 데이타 구분자는 ^ 이며, 구분자로 데이타를 파싱합니다.
                /*
                    - 복호화된 데이타 구성
                    가상주민번호확인처리결과코드^가상주민번호^성명^중복확인값(DupInfo)^연령정보^성별정보^생년월일(YYYYMMDD)^내외국인정보^고객사 요청 Sequence
                */
                // $arrData = split("\^", $sDecData);
                $arrData = explode("^", $sDecData);
                $iCount = count($arrData);

                if ($iCount >= 5) {

                    /*
                        다음과 같이 사용자 정보를 추출할 수 있습니다.
                        사용자에게 보여주는 정보는, '이름' 데이타만 노출 가능합니다.

                        사용자 정보를 다른 페이지에서 이용하실 경우에는
                        보안을 위하여 암호화 데이타($sEncData)를 통신하여 복호화 후 이용하실것을 권장합니다. (현재 페이지와 같은 처리방식)

                        만약, 복호화된 정보를 통신해야 하는 경우엔 데이타가 유출되지 않도록 주의해 주세요. (세션처리 권장)
                        form 태그의 hidden 처리는 데이타 유출 위험이 높으므로 권장하지 않습니다.
                    */

                    $strResultCode = $arrData[0];            // 결과코드
                    if ($strResultCode == 1) {
                        $strCPRequest = $arrData[8];            // CP 요청번호

                        if ($sCPRequest == $strCPRequest) {

                            $sRtnMsg = __("사용자 인증 성공");

                            $strVno = $arrData[1];    // 가상주민번호 (13자리이며, 숫자 또는 문자 포함)
                            $strUserName = $arrData[2];    // 이름
                            $strDupInfo = $arrData[3];    // 중복가입 확인값 (64Byte 고유값)
                            $strAgeInfo = $arrData[4];    // 연령대 코드 (개발 가이드 참조)
                            $strGender = $arrData[5];    // 성별 코드 ()
                            $strBirthDate = $arrData[6];    // 생년월일 (YYYYMMDD)
                            $strNationalInfo = $arrData[7];    // 내/외국인 정보 (0:내국인; 1:외국인)

                        } else {
                            $sRtnMsg = sprintf(__("CP 요청번호 불일치 : 세션에 넣은 %s 데이타를 확인해 주시기 바랍니다."), $sCPRequest);
                        }
                    } else {
                        $sRtnMsg = sprintf(__("리턴값 확인 후, NICE신용평가정보 개발 담당자에게 문의해 주세요. [%s]"), $strResultCode);
                    }

                } else {
                    $sRtnMsg = __("리턴값 확인 후, NICE신용평가정보 개발 담당자에게 문의해 주세요.");
                }

            }
        } else {
            $sRtnMsg = __("처리할 암호화 데이타가 없습니다.");
        }

        Logger::info($sRtnMsg);

        $db = \App::load('DB');
        // TODO: 회원 가입시에만 dupeinfo 체크 해야함
        // list($chkCount) = $db->fetch("SELECT count(memId) FROM " . DB_MEMBER . " WHERE dupeinfo = '$strDupInfo'");

        Session::del('sess_OrderNo');

        $ssCallType = Session::get("sess_callType");

        if ($strAgeInfo >= 6) {
            Session::set('adult', 1);
        } else {
            Session::del('adult');
        }

        // 본인인증에서 만14세 미만 회원가입 허용상태
        // TODO: 14세 미만 가입 모듈 추가 필요 시즌4에 있음
        //        $mUnder14 = Core::loader('memberUnder14Join');
        //        $under14Code = $mUnder14->joinSelfCert($strBirthDate);
        //
        //        if ($ssCallType == "adultcheck" || $ssCallType == "adultcheckmobile") {
        //            if ($_SESSION['adult']) {
        //                $returnUrl = $_POST["param_r1"] ? $_POST["param_r1"] : "../../index.php";
        //                msg("성인 인증되었습니다.");
        //                if ($ssCallType == "adultcheckmobile") {
        //                    $returnUrl = "http://" . $_SERVER['HTTP_HOST'] . "/m/";
        //                } else {
        //                    $returnUrl = "../../index.php";
        //                }
        //                go($returnUrl, "parent");
        //            } else {
        //                $returnUrl = $_POST["param_r1"] ? $_POST["param_r1"] : "../../index.php";
        //                unset($_SESSION['adult']);
        //                msg("성인 인증에 실패했습니다.");
        //                if ($ssCallType == "adultcheckmobile") {
        //                    $returnUrl = "http://" . $_SERVER['HTTP_HOST'] . "/m/";
        //                } else {
        //                    $returnUrl = "../../index.php";
        //                }
        //                go($returnUrl, "parent");
        //            }
        //            exit();
        //        }

        $this->setData('sCPRequest', $sCPRequest);
        $this->setData('strCPRequest', $strCPRequest);
        $this->setData('ssCallType', $ssCallType);
        $this->setData('joinGubun', $joinGubun);
        $this->setData('strResultCode', $strResultCode);
        $this->setData('sRtnMsg', $sRtnMsg);
        $this->setData('strUserName', $strUserName);
        $this->setData('strBirthDate', $strBirthDate);
        $this->setData('strGender', ($strGender) ? 'M' : 'W');
        $this->setData('strDupInfo', $strDupInfo);
        $this->setData('strAgeInfo', $strAgeInfo);
        $this->setData('strNationalInfo', $strNationalInfo);
        //        $this->setData('chkCount', $chkCount);	// TODO: 현재 회원가입 쪽 처리하는 것이 아니기 때문에 우선 하드코딩. 프론트 회원가입 기획 나오면 전체적으로 수정 필요
        $this->setData('chkCount', 0);
        //        $this->setData('nice_minoryn', $ipin['nice_minoryn']); // TODO: 시즌3는 성인인증 설정 값이 없음.
        $this->setData('nice_minoryn', '');
        $this->setData('strBirthDate', substr($strBirthDate, 0, 4));
        //$this->setData('under14Code', $under14Code);  // TODO: 14세 미만 모듈 추가하면 적용할 것
        $this->setData('under14Code', '');

        // TODO: try catch 처리해서 오류날 경우 로그인페이지로 리다이렉트 또는 이전 페이지
        // TODO: nice 모듈 동작은 릴레이 요청으로 로컬에서도 되게끔 할 것...
    }
}
