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

use App;
use Request;
use UserFilePath;

/**
 * Class 회원가입항목설정
 * @package Controller\Admin\Policy
 * @author  yjwee
 */
class MemberJoinEventConfigController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $this->callMenu('member', 'member', 'joinEventConfig');
        //$mode = gd_isset(Request::get()->get('mode'), 'order');
        $eventType = gd_isset(Request::get()->get('eventType'), 'order');

        $request = \App::getInstance('request');

        $couponAdmin = App::load('\\Component\\Coupon\\CouponAdmin');
        $joinCouponArr = $couponAdmin->getAutoCouponUsable('join');
        $couponBenefit = $couponAdmin->convertCouponArrData($joinCouponArr);
        $this->setData('couponBenefit', $couponBenefit);

        //Request::get()->clear();
        unset($joinCouponArr);

        $mileageBasic = gd_policy('member.mileageBasic');
        $mileageGive = gd_policy('member.mileageGive');
        $mileageBenefit['unit'] = gd_isset($mileageBasic['unit']); // 마일리지 단위
        $mileageBenefit['payUsableFl'] = gd_isset($mileageBasic['payUsableFl'], 'n'); // 마일리지 사용 여부
        $mileageBenefit['giveFl'] = gd_isset($mileageGive['giveFl'], 'n'); // 마일리지 지급 여부
        $mileageBenefit['joinAmount'] = gd_isset($mileageGive['joinAmount'], 0); // 신규회원 가입 시 마일리지
        $mileageBenefit['emailFl'] = gd_isset($mileageGive['emailFl'], 'n'); // 이메일 수신동의 시 여부
        $mileageBenefit['emailAmount'] = gd_isset($mileageGive['emailAmount'], 0); // 이메일 수신동의 시 마일리지
        $mileageBenefit['smsFl'] = gd_isset($mileageGive['smsFl'], 'n'); // SMS 수신동의 시 여부
        $mileageBenefit['smsAmount'] = gd_isset($mileageGive['smsAmount'], 0); // SMS 수신동의 시 마일리지
        $this->setData('mileageBenefit', $mileageBenefit);

        $checked = [];

        if($eventType == 'order') {
            $data = gd_policy('member.joinEventOrder');
            gd_isset($data['useFl'], 'n'); // 주문 간단 가입 사용 설정
            gd_isset($data['deviceType'], 'all'); // 진행 범위
            gd_isset($data['couponNo'], ''); // 쿠폰 혜택
            gd_isset($data['bannerFl'], 'y'); // 배너 사용 설정
            gd_isset($data['bannerImageType'], 'basic'); // 배너 설정

            $checked['useFl'][$data['useFl']] =
            $checked['deviceType'][$data['deviceType']] =
            $checked['bannerFl'][$data['bannerFl']] =
            $checked['bannerImageType'][$data['bannerImageType']] = 'checked="checked"';

        } else {
            $data = gd_policy('member.joinEventPush');
            gd_isset($data['pushFl'], 'n'); // 회원가입 유도 푸시 사용 설정
            gd_isset($data['applySameFl'], 'y'); // 적용 범위
            gd_isset($data['pushType'], 'all'); // 노출 시점
            gd_isset($data['pushCnt'], '3');
            gd_isset($data['position'], 'right'); // 노출 위치
            gd_isset($data['iconType'], 'basic'); // 아이콘 설정
            gd_isset($data['pushDescriptionType'], 'text'); // 푸쉬 내용 설정
            gd_isset($data['bgColor'], '#ffffff'); // 배경 색상
            gd_isset($data['textColor'], '#000000'); // 텍스트 색상
            if(empty($data['applyPc']) && empty($data['applyMobile'])) {
                $data['applyPc'] = implode(STR_DIVISION, ['main', 'goodsView', 'goodsList', 'search', 'cart', 'orderWrite', 'boardList', 'login', 'searchOrder']); // 적용 범위
                $data['applyMobile'] = implode(STR_DIVISION, ['main', 'goodsView', 'goodsList', 'search', 'cart', 'orderWrite', 'boardList', 'login', 'searchOrder']); // 적용 범위
            }
            $checked['pushFl'][$data['pushFl']] =
            $checked['applySameFl'][$data['applySameFl']] =
            $checked['pushType'][$data['pushType']] =
            $checked['position'][$data['position']] =
            $checked['iconType'][$data['iconType']] =
            $checked['pushDescriptionType'][$data['pushDescriptionType']] = 'checked="checked"';
            $tmpApplyPc = explode(STR_DIVISION, $data['applyPc']);
            foreach($tmpApplyPc as $val) {
                $checked['applyPc'][$val] = 'checked="checked"';
            }
            $tmpApplyMobile = explode(STR_DIVISION, $data['applyMobile']);
            foreach($tmpApplyMobile as $val) {
                $checked['applyMobile'][$val] = 'checked="checked"';
            }
        }
        Request::get()->set('couponSaveType', 'auto');
        Request::get()->set('couponEventType', 'joinEvent');
        $couponAdminList = $couponAdmin->getCouponAdminList();
        $couponData = [];
        foreach ($couponAdminList['data'] as $index => $item) {
            $couponData[$item['couponNo']] = $item;
        }

        unset($couponAdminList);
        /** set view data */

        $this->addCss([
            '../script/jquery/colorpicker-master/jquery.colorpicker.css',
        ]);
        $this->addScript([
            'jquery/colorpicker-master/jquery.colorpicker.js',
        ]);

        $this->setData('bannerImagePath', UserFilePath::data('join_event')->www());
        $this->setData('eventType', $eventType);
        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('couponData', $couponData);

    }
}
