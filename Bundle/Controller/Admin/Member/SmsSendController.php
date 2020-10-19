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

use Component\Code\Code;
use Component\Godo\GodoSmsServerApi;
use Component\Member\Group\Util;
use Component\Member\MemberDAO;
use Component\Sms\Sms;
use Component\Sms\SmsAdmin;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;

/**
 * SMS 개별/전체 발송
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SmsSendController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /**
         * page navigation
         */
        if (gd_is_provider()) { // 공급사관리
            $this->callMenu('order', 'order', 'smsSend');
        } else {
            $this->callMenu('member', 'sms', 'send');
        }
        $logger = \App::getInstance('logger');
        $request = \App::getInstance('request');
        $request->request()->set('mallSno', DEFAULT_MALL_NUMBER);
        $smsAdmin = new SmsAdmin();
        $replaceCodeGroup = [
            'goods'     => '상품',
            'member'    => '회원',
            'order'     => '주문',
            'promotion' => '프로모션',
            'board'     => '게시판',
        ];
        $smsContents = '';
        $receiveTotal = $rejectCount = 0;
        $receiverData = $reSenderData = [];
        $popupMode = false;
        $receiverMode = 'select';

        // SMS 발신번호 사전 등록 번호 정보
        $smsCallNum = $smsAdmin->getSmsCallNum();
        $godoSms = new GodoSmsServerApi();
        $smsPreRegister = $godoSms->checkSmsCallNumber($smsCallNum);
        $memberGroupNames = Util::getGroupName();

        if ($request->get()->has('opener')) {
            $popupMode = true;
            $opener = $request->get()->get('opener', '');
            $this->setData('opener', $opener);
            $receiverKeys = $request->get()->get('receiverKeys', []);
            $countReceiverKeys = count($receiverKeys);
            if ($opener === 'promotion') {
                if ($countReceiverKeys < 1) {
                    throw new AlertOnlyException('Not found promotion event keys');
                }
                $getSmsContentsByPromotionEvents = function ($keys) {
                    $service = \App::load('Component\\Goods\\GoodsAdmin');
                    $eventAll = $service->getAdminListDisplayTheme('event')['data'];
                    $messages = [];
                    $mobileShopConfig = ComponentUtils::getPolicy('mobile.config');
                    StringUtils::strIsSet($mobileShopConfig['mobileShopFl'], 'n');
                    foreach ($eventAll as $index => $event) {
                        if (in_array($event['sno'], $keys)) {
                            $messages[] = $event['themeNm'];
                            $messages[] = DateTimeUtils::dateFormat('m/d', $event['displayStartDate']) . '~' . DateTimeUtils::dateFormat('m/d', $event['displayEndDate']);
                            if ($mobileShopConfig['mobileShopFl'] === 'n') {
                                $event['mobileFl'] = 'n';
                            }
                            $url = $event['mobileFl'] === 'n' ? $event['eventSaleUrl'] : $event['MobileEventSaleUrl'];
                            $messages[] = GodoUtils::shortUrl($url);
                            $messages[] = '';
                        }
                    }
                    array_unshift($messages, '[{rc_mallNm}]');
                    array_pop($messages);

                    return StringUtils::htmlSpecialCharsStripSlashes(join("\n", $messages));
                };
                $smsContents = $getSmsContentsByPromotionEvents($receiverKeys);
            } elseif ($opener === 'member') {
                if ($countReceiverKeys < 1) {
                    throw new AlertOnlyException('Not found member number.');
                }
                foreach ($receiverKeys as $receiverKey) {
                    $member = MemberDAO::getInstance()->selectMemberWithGroup($receiverKey, 'memNo');
                    ArrayUtils::unsetDiff($member, explode(',', 'memId,nickNm,memNo,memNm,cellPhone,smsFl,appFl,maillingFl,email,groupSno,snsTypeFl,groupNm'));
                    $member['smsFlText'] = $member['smsFl'] == 'y' ? '수신' : '거부';
                    $member['maillingFlText'] = $member['maillingFl'] == 'y' ? '수신' : '거부';
                    $reSenderData[] = $member;
                }
            } elseif ($opener === 'order') {
                $this->setData('receiverCount', count($request->get()->get('receiverKeys', [])));
                $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');
                $this->setData('countSmsFlN', $orderAdmin->getCountSmsFlByOrderNo($request->get()->get('receiverKeys', [])));
            } elseif ($opener === 'goods') {
                //상품 재입고 알림
                $this->setData('receiverCount', count($request->get()->get('receiverKeys', [])));
                $getSmsContentsByGoodsRestock = function () {
                    $returnData = '';
                    $db = \App::getInstance('DB');
                    $db->strField = '*';
                    $db->strWhere = 'smsType = \'user\' AND smsAutoCode LIKE CONCAT(\'01007501\', \'%\')';
                    $query = $db->query_complete();
                    $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SMS_CONTENTS . ' ' . implode(' ', $query);
                    list($resultData) = $db->query_fetch($strSQL);
                    $returnData = StringUtils::htmlSpecialCharsStripSlashes($resultData['contents']);

                    return $returnData;
                };

                $smsContents = $getSmsContentsByGoodsRestock();
            }
        } else if ($request->request()->has('receiverMemNo') || ($request->request()->has('receiverNm') && $request->request()->has('receiverPhone'))) {
            $popupMode = true;
            // 회원 번호가 있는 경우 회원 정보 추출
            if (empty($request->request()->get('receiverMemNo')) === false) {
                $receiverData = $this->getMemberSmsInfo($request->request()->get('receiverMemNo'));
                if (empty($receiverData) === true) {
                    $receiverMode = 'select';
                } else {
                    // 주문 상세정보에서 주문자 sms 보내기 시 주문자 휴대전화번호가 다를 경우!
                    // 회원정보의 이름, 휴대전화번호와 receiverNm, receiverPhone 값이 다를 경우 guest 로 처리
                    if (empty($request->request()->get('receiverNm')) === false && empty($request->request()->get('receiverPhone')) === false) {
                        if($receiverData[0]['memNm'] != $request->request()->get('receiverNm') || $receiverData[0]['cellPhone'] != $request->request()->get('receiverPhone')) {
                            $receiverData[0]['memNo'] = '0';
                            $receiverData[0]['memNm'] = $request->request()->get('receiverNm');
                            $receiverData[0]['smsFl'] = 'y';
                            $receiverData[0]['cellPhone'] = $request->request()->get('receiverPhone');
                            $receiverMode = 'guest';
                        } else {
                            $receiverMode = 'member';
                        }
                    } else {
                        $receiverMode = 'member';
                    }
                }
            } elseif (empty($request->request()->get('receiverNm')) === false && empty($request->request()->get('receiverPhone')) === false) {
                // 비회원인 경우에 대한 정보 (주문리스트등에서 보내는 경우)
                if (is_array($request->request()->get('receiverNm'))) {
                    if (count($request->request()->get('receiverNm')) == count($request->request()->get('receiverPhone'))) {
                        $receverNm = $request->request()->get('receiverNm');
                        $receiverPhone = $request->request()->get('receiverPhone');
                        foreach ($receverNm as $key => $val) {
                            $receiverData[$key]['memNo'] = '0';
                            $receiverData[$key]['memNm'] = $receverNm[$key];
                            $receiverData[$key]['smsFl'] = gd_isset($request->request()->get('smsFl'));
                            $receiverData[$key]['cellPhone'] = $receiverPhone[$key];
                        }
                    } else {
                        throw new Exception(__('잘못됐습니다.'));
                    }
                } else {
                    $receiverData[0]['memNo'] = '0';
                    $receiverData[0]['memNm'] = $request->request()->get('receiverNm');
                    $receiverData[0]['smsFl'] = gd_isset($request->request()->get('smsFl'));
                    $receiverData[0]['cellPhone'] = str_replace('-', '', $request->request()->get('receiverPhone'));
                }
                $receiverMode = 'guest';
            }
        }

        if ($request->post()->get('smsLogSno', 0) > 0) {
            $componentSmsLog = \App::load('Component\\Sms\\SmsLog');
            $smsLog = $componentSmsLog->getSmsLog('*', $request->post()->get('smsLogSno'));     // 재전송 문자내역 조회
            $smsContents = StringUtils::htmlSpecialCharsStripSlashes($smsLog['contents']);     // 재전송 메시지 설정
            $smsSendList = $componentSmsLog->getSmsSendList($request->post()->all())['data'];     // 재전송 문자내역 발송내역 조회
            $hasGuest = $componentSmsLog->hasGuestBySendList($smsSendList);     // 발송내역 중 비회원 발송내역 확인
            $isReSendAll = $request->post()->get('mode', '') == 'resend_all_member';
            $selectedReSendSno = $request->post()->get('sno', []);
            $isReSendSelect = ($request->post()->get('mode', '') == 'resend_select_member') && (count($selectedReSendSno) > 0);
            $this->setData('smsLogSno', $request->post()->get('smsLogSno', 0));
            $logger->info(sprintf('Sms resend. mode[%s], has guest[%b], count(smsSendList)[%d]', $request->post()->get('mode', ''), $hasGuest, count($smsSendList)), $request->request()->all());
            if ($isReSendAll && $hasGuest) {
                $receiveTotal = count($smsSendList);
                $receiverMode = 'guest';
                $this->setData('arrSmsSendListSno', ArrayUtils::getSubArrayByKey($smsSendList, 'sno'));
                $this->setData('receiverCount', $receiveTotal);
            } elseif ($isReSendAll && !$hasGuest) {
                $componentMember = \App::load('Component\\Member\\Member');
                $arrMember = [];
                if (count($smsSendList) > 0) {
                    $arrMember = $componentMember->listsWithCoupon(['chk' => ArrayUtils::getSubArrayByKey($smsSendList, 'memNo')]);
                }
                list($receiveTotal, $rejectCount, $reSenderData) = $componentSmsLog->getMemberForReSend($arrMember);
            } elseif ($isReSendSelect && $hasGuest) {
                $receiveTotal = count($selectedReSendSno);
                $receiverMode = 'guest';
                $this->setData('arrSmsSendListSno', $selectedReSendSno);
                $this->setData('receiverCount', $receiveTotal);
            } elseif ($isReSendSelect && !$hasGuest) {
                foreach ($smsSendList as $index => $log) {
                    if (!in_array($log['sno'], $selectedReSendSno)) {
                        unset($smsSendList[$index]);
                    }
                }
                $componentMember = \App::load('Component\\Member\\Member');
                $arrMember = [];
                if (count($smsSendList) > 0) {
                    $arrMember = $componentMember->listsWithCoupon(['chk' => ArrayUtils::getSubArrayByKey($smsSendList, 'memNo')]);
                }
                list($receiveTotal, $rejectCount, $reSenderData) = $componentSmsLog->getMemberForReSend($arrMember);
            } else {
                $logger->info(sprintf('Sms resend error. mode[%s], has guest[%s]', $request->post()->get('mode', ''), $hasGuest), $selectedReSendSno);
            }
        }

        // --- 관리자 디자인 템플릿
        if ($popupMode === true) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        } else {
            $this->getView()->setDefine('layout', 'layout_basic.php');
        }

        // 공급사와 공통으로 템플릿 사용
        $this->getView()->setPageName('member/sms_send.php');

        $this->addScript(
            [
                'sms.js?ts=' . time(),
                'jquery/jquery.form.min.js',
            ]
        );

        // SMS 포인트 Sync
        Sms::saveSmsPoint();
        $code = new Code();
        /**
         *   set view data
         */
        $this->setData('memGroupNm', gd_htmlspecialchars($memberGroupNames));
        $this->setData('sms080Policy', ComponentUtils::getPolicy('sms.sms080'));
        $this->setData('smsStringLimit', Sms::SMS_STRING_LIMIT);
        $this->setData('lmsStringLimit', Sms::LMS_STRING_LIMIT);
        $this->setData('smsForbidTime', Sms::SMS_FORBID_TIME);
        $this->setData('lmsPoint', Sms::LMS_POINT);
        $this->setData('smsCallNum', $smsCallNum);
        $this->setData('smsPreRegister', $smsPreRegister);
        if ($this->getData('receiverCount') < 1) {
            $this->setData('receiverCount', count($receiverData));
        }
        $this->setData('receiverData', $receiverData);
        $this->setData('receiverMode', $receiverMode);
        $this->setData('popupMode', $popupMode);
        $this->setData('smsContents', $smsContents);
        $this->setData('reSenderData', $reSenderData);
        $this->setData('receiveTotal', $receiveTotal);
        $this->setData('rejectCount', $rejectCount);
        $this->setData('smsContentsGroupCode', $code->getGroupItems('01007'));
        $this->setData('smsAutoCode', $popupMode ? 'order' : '');
        $this->setData('replaceCodeGroupKey', $popupMode ? '' : 'member');
        $this->setData('replaceCodeGroup', $replaceCodeGroup);
    }

    /**
     * 회원의 SMS 관련 정보
     *
     * @param mixed $memNo 회원 sno
     *
     * @return array|boolean 회원 정보
     */
    public function getMemberSmsInfo($memNo)
    {
        /** @var \Bundle\Component\Member\Member $member */
        $member = \App::load('\\Component\\Member\\Member');

        // 회원정보가 배열로 넘어오는 경우에 대한 처리
        if (is_array($memNo)) {
            $data = $member->getMember(null, 'memNo IN (\'' . implode('\',\'', array_unique($memNo)) . '\')', 'memNo, memNm, smsFl, cellPhone', true);
        } else {
            $data = $member->getMember($memNo, 'memNo', 'memNo, memNm, smsFl, cellPhone', true);
        }

        if (empty($data) === false) {
            // 휴대폰 번호 없는 회원 걸러내기
            foreach ($data as $key => $val) {
                if (empty($val['cellPhone']) === true) {
                    unset($data[$key]);
                }
            }

            return gd_htmlspecialchars_stripslashes($data);
        } else {
            return false;
        }
    }
}
