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

namespace Bundle\Controller\Admin\Member;

use Exception;

/**
 * 회원정보 수정 이벤트 등록
 *
 * @author haky <haky2@godo.co.kr>
 */
class MemberModifyEventRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $eventNo = $request->get()->get('eventNo', '');

        // 메뉴 설정
        if ($eventNo) { // 수정인 경우
            $mode = 'modify';
            $this->callMenu('member', 'member', 'modifyMemberEventModify');
        } else { // 등록인 경우
            $mode = 'register';
            $this->callMenu('member', 'member', 'modifyMemberEventRegister');
        }

        try {
            // 모듈 호출
            $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
            // 이벤트 상세정보
            $data = $modifyEvent->getMemberModifyEventInfo($eventNo);
            // 이벤트 노출 데이터 설정
            $eventInfo = $modifyEvent->setDisplayMemberModifyEventRegister($data);
            // 상점 구분
            $mallSno = $request->get()->get('mallSno', gd_isset($eventInfo['data']['mallSno'], DEFAULT_MALL_NUMBER));

            if (\Globals::get('gGlobal.isUse')) {
                foreach (\Globals::get('gGlobal.useMallList') as $val) {
                    if ($val['sno'] == $mallSno) {
                        $countryCode = $val['domainFl'];
                    }
                }
            }

            // 디폴트 값 설정
            gd_isset($countryCode, 'kr');
            gd_isset($eventInfo['data']['eventType'], 'modify');

            $eventInfo['data']['memberEventPopupView'] = ($eventInfo['data']['eventType'] == 'modify') ? 'member_modify_event_' . $countryCode . '_01.png' : 'member_life_event_' . $countryCode . '_01.png';
            $eventInfo['data']['memberEventPopupContent'] = ($eventInfo['data']['eventType'] == 'modify') ? 'member_modify_event_' . $countryCode . '_02.png' : 'member_life_event_ ' . $countryCode . '_02.png';

            //회원가입 항목관리 > 개인정보유효기간 설정값
            $policyService = new \Component\Policy\JoinItemPolicy();
            $policy = $policyService->getPolicy($mallSno);
            $expirationFl = ($policy['expirationFl']['use'] === 'y' || $policy['expirationFl']['require'] === 'y') ? 'y' : 'n';

        } catch (Exception $e) {
            throw $e;
        }

        $this->setData('mall', ['mallSno' => $mallSno, 'mallNm' => $countryCode]);
        $this->setData('mode', $mode);
        $this->setData('eventInfo', $eventInfo['data']);
        $this->setData('search', $eventInfo['search']);
        $this->setData('checked', $eventInfo['checked']);
        $this->setData('disabled', $eventInfo['disabled']);
        $this->setData('expirationFl', $expirationFl);
    }
}