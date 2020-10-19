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

use Component\Member\Group\GroupService;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\MemberGroup;
use Framework\Debug\Exception\AlertBackException;
use Request;
use Component\Coupon\CouponAdmin;

/**
 * Class 회원등급 수정 컨트롤러
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberGroupModifyController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException 오류메시지
     * @return void
     */
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        $this->callMenu('member', 'member', 'groupModify');
        try {
            if (Request::request()->has('sno') === false) {
                throw new AlertBackException(__('회원등급번호가 없습니다.'));
            }
            $sno = Request::request()->get('sno');
            if (!is_numeric($sno)) {
                throw new AlertBackException(__('유효한 회원등급번호가 아닙니다.'));
            }
            $groupService = new GroupService();
            $groupByView = $groupService->getGroup();

            //쿠폰 관련 데이터 가져오기
            $coupon = new CouponAdmin();
            $couponList = explode(INT_DIVISION, $groupByView['groupCoupon']);
            if(!empty($couponList[0])) {
                foreach ($couponList as $couponCode) {
                    $couponInfo[] = $coupon->getCouponInfo($couponCode);
                }
            }

            //--- 브랜드 정보
            $cate = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
            $getBrandData = $cate->getCategoryListSelectBox('y');

            $checked['groupImageGb'][$groupByView['groupImageGb']] =
            $checked['groupMarkGb'][$groupByView['groupMarkGb']] =
            $checked['deliveryFree'][$groupByView['deliveryFree']] =
            $checked['apprExclusionOfRatingFl'][$groupByView['apprExclusionOfRatingFl']] =
            $checked['overlapDcBankFl'][$groupByView['overlapDcBankFl']] = 'checked="checked"';

            $this->setData('defaultGroupInfo', GroupUtil::checkDefaultGroup($sno));
            $this->setData('mode', MemberGroup::MODE_MODIFY);
            $this->setData('data', $groupByView);
            $this->setData('checked', $checked);
            $this->setData('sno', $sno);
            $this->setData('settleGbData', GroupUtil::getSettleGbData());
            $this->setData('settleGbDataCheck', GroupUtil::matchSettleGbDataToString($groupByView['settleGb']));
            $this->setData('fixedRateOptionData', GroupUtil::getFixedRateOptionData());
            $this->setData('fixedRatePriceData', GroupUtil::getFixedRatePriceData());
            $this->setData('fixedOrderTypeData', GroupUtil::getFixedOrderTypeData('brand'));
            $this->setData('fixedOrderTypeAllData', GroupUtil::getFixedOrderTypeData());
            $this->setData('dcOptionData', GroupUtil::getDcOptionData());
            $this->setData('couponDataList', $couponInfo);
            $this->setData('getBrandCnt', $getBrandData['cnt']);
            $this->setData('getBrandData', $getBrandData['data']);
            $this->getView()->setPageName('member/member_group_register.php');
            $this->addScript(
                [
                    'member.js',
                    'jquery/jquery.multi_select_box.js',
                ]
            );
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
