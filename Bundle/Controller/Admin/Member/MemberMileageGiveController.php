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

namespace Bundle\Controller\Admin\Member;

use Component\Member\Group\Util as GroupUtil;
use Exception;
use Request;

/**
 * 회원의 마일리지 지급 설정 관리 페이지
 *
 * @author Ahn Jong-tae <qnibus@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MemberMileageGiveController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $this->callMenu('member', 'point', 'mileageGive');

            $data = gd_policy('member.mileageGive');

            gd_isset($data['giveFl'], 'y'); // 지급 여부
            gd_isset($data['giveType'], 'price'); // 지급 기준
            if ($data['giveType'] == 'price') { // 구매금액의 %
                gd_isset($data['goods'], 0); // %
            } else if ($data['giveType'] == 'priceUnit') { // 구매금액당 지급
                gd_isset($data['goodsPriceUnit'], 0); // 구매금액당
                gd_isset($data['goodsMileage'], 0); // 원 지급
            } else if ($data['giveType'] == 'cntUnit') { // 수량(개)당 지급
                gd_isset($data['cntMileage'], 0); // 원 지급
            }
            gd_isset($data['excludeFl'], 'y'); // 마일리지 사용시 지급여부
            gd_isset($data['delayFl'], ''); // 지급 유예기능 사용여부
            gd_isset($data['delayDay'], '7'); // 지급 유예일자
            gd_isset($data['joinFl'], 'y'); // 신규회원 가입 시 여부
            gd_isset($data['joinAmount'], 0); // 신규회원 가입 시 마일리지
            gd_isset($data['emailFl'], 'n'); // 이메일 수신동의 시 여부
            gd_isset($data['emailAmount'], 0); // 이메일 수신동의 시 마일리지
            gd_isset($data['smsFl'], 'n'); // SMS 수신동의 시 여부
            gd_isset($data['smsAmount'], 0); // SMS 수신동의 시 마일리지
            gd_isset($data['recommJoinAmount'], 0); // 신규회원에게 마일리지로 지급
            gd_isset($data['recommAmount'], 0); // 등록된 추천인 아이디에 마일리지로 지급
            gd_isset($data['recommCountFl'], 'n'); // 등록된 추천인 아이디에 지급 횟수 사용 여부
            gd_isset($data['recommCount'], 0); // 등록된 추천인 아이디에 지급 횟수
            gd_isset($data['birthAmount'], 0); // 생일인 회원에게 마일리지

            $checked = [];
            $checked['giveFl'][$data['giveFl']] =
            $checked['giveType'][$data['giveType']] =
            $checked['excludeFl'][$data['excludeFl']] =
            $checked['delayFl'][$data['delayFl']] =
            $checked['joinFl'][$data['joinFl']] =
            $checked['emailFl'][$data['emailFl']] =
            $checked['smsFl'][$data['smsFl']] =
            $checked['recommCountFl'][$data['recommCountFl']] = 'checked="checked"';

            $selected['delayDay'][$data['delayDay']] = 'selected="selected"';

            $mileageBasic = gd_policy('member.mileageBasic');
            $displayMileageBasic[] = '판매가';
            if ($mileageBasic['optionPrice'] == 1) {
                $displayMileageBasic[] = '옵션가';
            }
            if ($mileageBasic['addGoodsPrice'] == 1) {
                $displayMileageBasic[] = '추가상품가';
            }
            if ($mileageBasic['textOptionPrice'] == 1) {
                $displayMileageBasic[] = '텍스트옵션가';
            }
            if ($mileageBasic['goodsDcPrice'] == 1) {
                $displayMileageBasic[] = '상품할인가';
            }
            if ($mileageBasic['memberDcPrice'] == 1) {
                $displayMileageBasic[] = '회원할인가';
            }
            if ($mileageBasic['couponDcPrice'] == 1) {
                $displayMileageBasic[] = '쿠폰할인가';
            }
            $data['mileageBasic'] = implode(' + ', $displayMileageBasic);

            /**
             * 회원그룹 혜택 정보
             * @var \Bundle\Component\Member\MemberGroup $memberGroup
             */
            $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
            $groupList = $memberGroup->getGroupList();

            $joinItem = gd_policy('member.joinitem');
            $data['memberJoinItemRecommIdUse'] = (isset($joinItem['recommId']) && $joinItem['recommId']['use'] == 'y');

            $order = gd_policy('order.status');
            $tmp = [];
            if ($order['payment']['mplus'] === 'y') {
                $tmp[] = $order['payment']['p1']['admin'];
            } elseif ($order['delivery']['mplus'] === 'y') {
                $tmp[] = $order['delivery']['d2']['admin'];
            } elseif ($order['settle']['mplus'] === 'y') {
                $tmp[] = $order['settle']['s1']['admin'];
            }
            $mileageGiveStatus = implode(', ', $tmp);

            if (Request::get()->get('popupMode')) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }

            $this->setData('mileageBasic', $mileageBasic);
            $this->setData('data', $data);
            $this->setData('checked', $checked);
            $this->setData('selected', $selected);
            $this->setData('groupList', $groupList['data']);
            $this->setData('mileageGiveStatus', $mileageGiveStatus);
            $this->setData('fixedOrderTypeData', GroupUtil::getFixedOrderTypeData());
        } catch (Exception $e) {
            throw $e;
        }
    }
}
