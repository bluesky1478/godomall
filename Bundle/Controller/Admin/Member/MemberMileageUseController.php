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

use Globals;
use Exception;

/**
 * 회원의 마일리지 사용 설정 관리 페이지
 *
 * @author Ahn Jong-tae <qnibus@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 * @author Wee Yeongjong <yeongjong.wee@godo.co.kr>
 */
class MemberMileageUseController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            /** page navigation */
            $this->callMenu('member', 'point', 'mileageUse');

            // 마일리지 기본 설정 정보
            $basicData = Globals::get('gSite.member.mileageBasic');

            // 마일리지 사용 설정 정보
            $data = Globals::get('gSite.member.mileageUse');

            // 기본값 세팅
            gd_isset($data['minimumHold'], '0');
            gd_isset($data['orderAbleLimit'], '0');
            gd_isset($data['standardPrice'], 'goodsPrice');
            gd_isset($data['minimumLimit'], '0');
            gd_isset($data['maximumLimit'], '0');
            gd_isset($data['maximumLimitUnit'], 'percent');
            gd_isset($data['maximumLimitDeliveryFl'], 'y');

            /** set checkbox, select property */
            $checked['standardPrice'][$data['standardPrice']] =
            $checked['maximumLimitDeliveryFl'][$data['maximumLimitDeliveryFl']] = 'checked="checked"';

            $selected = [];
            $selected['maximumLimitUnit'][$data['maximumLimitUnit']] = 'selected="selected"';

            /** set view data */
            $this->setData('data', $data);
            $this->setData('basicData', $basicData);
            $this->setData('checked', $checked);
            $this->setData('selected', $selected);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
