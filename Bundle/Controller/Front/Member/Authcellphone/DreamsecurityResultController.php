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

namespace Bundle\Controller\Front\Member\Authcellphone;

use Component\Member\Member;
use Component\Member\Util\MemberUtil;
use Exception;
use Framework\Application\Bootstrap\Log;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertRedirectCloseException;
use Framework\Utility\NumberUtils;
use Framework\Utility\DateTimeUtils;

/**
 * Class 드림시큐리티 휴대폰 본인확인 모듈 사용자 인증 정보 결과 페이지
 * @package Controller\Front\Member\Ipin
 * @author  yjwee
 */
class DreamsecurityResultController extends \Controller\Front\Controller
{
    public function index()
    {
        $encryptor = \App::getInstance('encryptor');
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger')->channel(Log::CHANNEL_DREAMSECURITY);
        $sDevelopedData = $request->post()->get('sDevelopedData', '');

        $resultCheckAge = $ssReturnUrl = '';

        try {
            $authData = gd_policy('member.auth_cellphone');
            $joinPolicy = gd_policy('member.join');
            // 본인인증 결과 처리
            if (empty($sDevelopedData)) {
                $logger->error('Not found request parameters');
                throw new \AlertCloseException('응답이 없습니다.');
            }
            $getData = preg_split('/\$/', iconv('EUC-KR', 'UTF-8', $sDevelopedData));

            $strConInfo = $getData[0];                // 여기서는 CI 값 확인 하지 않음
            $strDupInfo = $getData[1];                // 중복가입 확인값 (64Byte 고유값)
            $strDupInfoEnc = $encryptor->encrypt($getData[1]);        // 중복가입 확인 값을 암호화
            $strPhoneNum = $getData[2];                // 전화번호
            $strPhoneCorp = $getData[3];                // 이동 통신사
            //$strBirthDate		= $getData[4];				// 생년월일 예)860406 - 사용하지 않음
            $strGender = $getData[5];                // 성별 값 (주민번호 앞자리)
            $strNationalInfo = $getData[6];                // 내/외국인 정보 (0 - 내국인, 1 - 외국인)
            $strUserName = $getData[7];                // 이름
            $strReqNum = $getData[8];                // 요청번호
            $strReqDate = $getData[9];                // 요청일시
            $strVnoEnc = $encryptor->encrypt($strPhoneNum . STR_DIVISION . $strPhoneCorp);    // 가상번호

            $strBirthDate = $request->post()->get('ibirth');            // 생년월일 예)19860406
            $clntReqNum = $request->post()->get('clntReqNum');        // 요청번호 : $strReqNum 값과 같아야 정상인증 (위변조방지)
            $strResult = $request->post()->get('result');            // 성공시, success
            $strResultCode = $request->post()->get('resultCd');        // 성공시, 00

            // 아래 Exception 2개는 view에서 스크립트로 체크하던 부분으로 오류이기때문에 컨트롤러에서 Exception으로 처리하도록 수정함
            if ($strReqNum != $clntReqNum) {
                $logger->error('Wrong client request number');
                throw new AlertCloseException('잘못된 접근입니다!!');
            }
            if ($strResultCode == '0000') {
                $strResultCode = '00';
            }
            if ($strResultCode != '00') {
                $logger->error('Fail cellphone auth. resultCode is not 00');
                throw new AlertCloseException('휴대폰인증이 실패했습니다.\n\n');
            }

            $session->set(
                Member::SESSION_DREAM_SECURITY, [
                    'CI'           => $strConInfo,
                    'DI'           => $strDupInfo,
                    'phone'        => $strPhoneNum,
                    'phoneService' => $strPhoneCorp,
                    'gender'       => $strGender,
                    'national'     => $strNationalInfo,
                    'name'         => $strUserName,
                    'reqNum'       => $strReqNum,
                    'reqDate'      => $strReqDate,
                    'ibirth'       => $strBirthDate,
                    'clntReqNum'   => $clntReqNum,
                    'result'       => $strResult,
                    'resultCd'     => $strResultCode,
                ]
            );
            $logger->info('set session auth info');

            $strAge = NumberUtils::ageCalculator($strBirthDate);    // 만나이
            // $strAgeInfo = age_code($strAge);        // 연령대 코드 (아이핀 것을 기준)
            $session->set('sess_callType', $request->post()->get('ssCallType', $request->get()->get('ssCallType', '')));        // 분류값 sess_callType (세션)
            $session->set('sess_returnUrl', $request->post()->get('returnUrl', $request->get()->get('returnUrl', '')));        // 분류값 sess_callType (세션)
            $ssCallType = $session->get('sess_callType');

            // 휴면회원 해제는 view 에서 callType 체크 후 location 이동만 하므로 서버에서 처리
            if ($ssCallType == 'wakeMember') {
                throw new AlertRedirectCloseException('인증 성공.', null, null, '/member/wake.php', 'opener');
            }

            if ($session->get('sess_returnUrl')) $ssReturnUrl = urldecode($session->get('sess_returnUrl'));

            // 인증 성공 여부 - 요청시 넘긴 요청번호(clntReqNum)와 결과에 포함된 요청번호(reqNum)가 일치해야 인증성공
            if ($clntReqNum != $strReqNum) {
                $sRtnMsg = '잘못된 접근입니다';
                $strResult = '';
                $strResultCode = '';
            } else {
                $sRtnMsg = '인증 성공';
            }
            $logger->info('DreamSecurity check success');

            // 성별 처리
            if ($strGender == 1 || $strGender == 3 || $strGender == 5 || $strGender == 7 || $strGender == 9) {
                $strGender = 'M';
            } else {
                $strGender = 'W';
            }
            $logger->info('DreamSecurity check gender');

            // 회원 존재 여부
            if ($strResult == 'success' && ($ssCallType == 'joinmember' || $ssCallType == 'joinmembermobile')) {
                // 회원 재가입 기간 체크
                if ($strDupInfo) {
                    if (MemberUtil::isReJoinByDupeinfo($strDupInfo) == false) {
                        throw new AlertCloseException('현재 가입하실 수 없는 상태입니다. 고객센터로 문의주시기 바랍니다.');
                    }

                    // 이마트 보안취약점 요청사항 인증시 사용한 핸드폰번호와 이름 추가로 체크함
                    if (MemberUtil::isExistsDupeInfo($strDupInfo, $strPhoneNum, $strUserName) == true) {
                        throw new AlertCloseException('이미 가입이 되어 있습니다.');
                    }
                }

                $resultCheckAge = MemberUtil::checkJoinAuth($strAge);
                if ($resultCheckAge == 'n') {
                    throw new AlertCloseException($joinPolicy['limitAge'] . '세 미만은 가입하실 수 없습니다.');
                }

                $session->set(Member::SESSION_CHECK_AGE_AUTH, $resultCheckAge);
            }
            $logger->info('DreamSecurity check exist');

            if ($ssCallType == 'certGuest' && $strResult == 'success'){
                if ($strAge < 15) {
                    $session->del(Member::SESSION_DREAM_SECURITY);
                    $this->js("top.location.href='../../order/certWarning.php';");
                }
                else {
                    $session->set('certGuest', ["guestAuthFl" => "y"]);
                }
            }

            //성인(현재 나이 20세 기준)인증 관련
            if ($strResult == 'success' && NumberUtils::ageCalculator($strBirthDate, 2) >= 20) {
                $session->set('certAdult', ["adultFl" => "y"]);
                $strAdult = 'y';
            } else {
                $strAdult = 'n';
            }

            //성인인증
            if ($ssCallType == 'certAdult' && $strResult == 'success') {
                if ($strAdult == 'y') {
                    if ($session->has('member')) {
                        $session->set('member.adultFl', "y");
                        $member = \App::load('Component\\Member\\Member');
                        $member->updateAdultInfo();
                    }
                } else {
                    throw new AlertCloseException('성인만 이용가능합니다.');
                }
            }
            $logger->info('Dreamsecurity check adult');
        } catch (AlertCloseException $e) {
            throw $e;
        } catch (AlertRedirectCloseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $logger->error($e->getMessage());
            throw new AlertOnlyException($e->getMessage());
        }

        $logger->info('Dreamsecurity end');

        // 연령 인증    TODO:인증체크
        /*if ($strResult == 'success' && $authData['minorFl'] == 'y') {
            if ($strAgeInfo >= gd_isset($authData['codeValue'], 6)) {
                $strMinor = 1;    // 1:true, 2:fales
            } else {
                $strMinor = 2;    // 1:true, 2:fales
            }
        }*/

        /**
         *   set view data
         */
        $this->setData('limitAge', $joinPolicy['limitAge']);
        $this->setData('checkAge', $resultCheckAge);
        $this->setData('clntReqNum', $clntReqNum);
        $this->setData('strReqNum', $strReqNum);
        $this->setData('callType', $ssCallType);
        $this->setData('age', $strAge);
        $this->setData('strPhoneCorp', $strPhoneCorp);
        $this->setData('strReqDate', $strReqDate);
        $this->setData('strRetCd', gd_isset($strResult));
        $this->setData('strRetDtlCd', gd_isset($strResultCode));
        $this->setData('strMsg', gd_isset($sRtnMsg));
        $this->setData('strName', gd_isset($strUserName));
        $this->setData('birthday', gd_isset($strBirthDate));
        $this->setData('sex', gd_isset($strGender));
        $this->setData('dupeInfo', gd_isset($strDupInfo));
        $this->setData('foreigner', gd_isset($strNationalInfo));
        $this->setData('paKey', gd_isset($strVnoEnc));
        $this->setData('phoneNum', gd_isset($strPhoneNum));
        $this->setData('memExist', gd_isset($memExist));
        $this->setData('adultFl', $strAdult);
        $this->setData('returnUrl', $ssReturnUrl);
        $this->setData('isMobile', $request->isMobile());

        $this->setData('birthYear', gd_isset(DateTimeUtils::dateFormat('Y', $strBirthDate)));
        $this->setData('birthMonth', gd_isset(DateTimeUtils::dateFormat('m', $strBirthDate)));
        $this->setData('birthDay', gd_isset(DateTimeUtils::dateFormat('d', $strBirthDate)));

        $this->getView()->setDefine('header', 'outline/_share_header.html');

        //        $this->setData('memAppFl', gd_isset($memAppFl));
        //        $this->setData('memRefuse', gd_isset($memRefuse));
        //        $this->setData('memHackDt', gd_isset($memHackDt));
        //        $this->setData('rejoinDay', gd_isset($cfgJoin['rejoin']));
        //        $this->setData('minorFl', gd_isset($authData['minorFl']));
        //        $this->setData('strMinor', gd_isset($strMinor));
    }
}
