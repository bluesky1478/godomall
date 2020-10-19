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

use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;

/**
 * Class 관리자-회원-메일 관리-자동메일관리 컨트롤러
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MailConfigAutoController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'mail', 'autoConfig');
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        $memberJoin = ComponentUtils::getPolicy('member.join');
        if (!$request->get()->has('mallSno')) {
            $request->get()->set('mallSno', DEFAULT_MALL_NUMBER);
        }
        $this->setData('isDefaultMall', $request->get()->get('mallSno') == DEFAULT_MALL_NUMBER);
        $this->setData('mallSno', $request->get()->get('mallSno'));

        $category = $request->get()->get('category', 'order');
        switch ($category) {
            case 'order':
                $type = $request->get()->get('type', 'order');
                break;
            case 'join':
                $type = $request->get()->get('type', 'join');
                break;
            case 'member':
                $type = $request->get()->get('type', 'sleepnotice');
                break;
            case 'point':
                $type = $request->get()->get('type', 'addmileage');
                break;
            case 'admin';
                $type = $request->get()->get('type', 'adminsecurity');
                break;
            default:
                throw new \Exception(__("자동메일설정 카테고리는 필수입니다."));
                break;
        }

        $activeTab[$category] = 'active';

        // 발송여부 라디오버튼 셋 초기화
        $autoSendRadioData = [
            'y' => '발송함',
            'n' => '발송안함',
        ];

        $categoryTypes = \Component\Mail\MailUtil::getAutoTemplateType($category);
        if ($request->get()->get('mallSno') != DEFAULT_MALL_NUMBER) {
            if ($category == 'member') {
                unset($categoryTypes['AGREEMENT'], $categoryTypes['AGREEMENT2YPERIOD']);
            } elseif ($category == 'order') {
                unset($categoryTypes['INCASH']);
            }
        }
        $typeConfig = \Component\Mail\MailUtil::getMailConfigAuto($category, $type, $request->get()->get('mallSno'));
        $typeTemplate = \Component\Mail\MailUtil::getAutoMailConfigTemplate($type, $request->get()->get('mallSno'));
        $typeRadio = SkinUtils::makeRadioBox('typeRadio', ArrayUtils::toLowercaseByKey($categoryTypes), $type);
        $typeAutoSendRadio = SkinUtils::makeRadioBox('autoSendFl', $autoSendRadioData, empty($typeConfig['autoSendFl']) ? 'n' : $typeConfig['autoSendFl']);
        $policy = ComponentUtils::getPolicy('basic.info');
        $typeSendMail = empty($typeConfig['senderMail']) ? $policy['email'] : $typeConfig['senderMail'];
        $checked['mailDisapproval'][$typeConfig['mailDisapproval']] = 'checked="checked"';
        if ($memberJoin['appUseFl'] == 'n' && ($memberJoin['under14Fl'] == 'n' || $memberJoin['under14Fl'] == 'no')) {
            $typeRadio = preg_replace('/\n/', '', $typeRadio);
            $typeRadio = preg_replace('/(.*)approval"(.*)/', '$1 approval" disabled="disabled" $2', $typeRadio);
        }
        $sendTargetSelect = '';
        if ($category == 'order' && $type != 'order') {
            $sendTargetSelectData = [
                3  => 3,
                7  => 7,
                15 => 15,
                30 => 30,
                90 => 90,
            ];
            // 주문/배송 관련 발송대상 셀렉트 박스 생성
            $sendTarget = StringUtils::strIsSet($typeConfig['sendTarget'], '');
            if ($sendTarget == '') {
                if ($typeRadio == 'incash') {
                    $sendTarget = 30;
                } else if ($typeRadio == 'delivery') {
                    $sendTarget = 90;
                }
            }
            $sendTargetSelect = SkinUtils::makeSelectBox('sendTarget', 'sendTarget', $sendTargetSelectData, '일', $sendTarget);
        }

        if ($category == 'join' && $type == 'qna') {
            if (isset($typeConfig['initFl']) === false) {
                $logger->info('선택된 발송 게시판이 존재하지 않습니다. 기본 제공 게시판을 발송게시판으로 선택합니다.');
                $boardInfo = \Component\Board\BoardAdmin::getBasicBoard('sno, bdNm', ' AND bdId!=\'event\'');
                $this->setData('boardInfo', $boardInfo);
            } else if (isset($typeConfig['boardInfo'])) {
                $this->setData('boardInfo', $typeConfig['boardInfo']);
            }
        }
        $sendType = gd_isset($typeConfig['sendType'], 'y');
        $sendMailTypeSelect = SkinUtils::makeSelectBox('sendType', 'sendType', ['n'=>'주문번호 기준 1회만 발송','y'=>'부분 배송 시 배송 건 별 발송'], null, $sendType);

        // 쇼핑몰 도메인 설정 여부
        $mallData = ComponentUtils::getPolicy('basic.info');
        if (empty($mallData['mallDomain']) === false) {
            $mallDomainCheck = true;
        } else {
            $mallDomainCheck = false;
        }


        $this->setData('useApprovalFlag', $memberJoin['appUseFl'] != 'n' || $memberJoin['under14Fl'] == 'y');
        $this->setData('checked', $checked);
        $this->setData('category', $category);
        $this->setData('type', $type);
        $this->setData('activeTab', $activeTab);
        $this->setData('typeTemplate', $typeTemplate);
        $this->setData('typeAutoSendRadio', $typeAutoSendRadio);
        $this->setData('sendTargetSelect', $sendTargetSelect);
        $this->setData('sendMailTypeSelect', $sendMailTypeSelect);
        $this->setData('typeRadio', $typeRadio);
        $this->setData('typeSendMail', $typeSendMail);
        $this->setData('mallDomainCheck', $mallDomainCheck);

        $this->addScript(['member.js']);

        if ($request->get()->get('popupMode', '') === 'yes') {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }
    }
}
