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
namespace Bundle\Controller\Admin\Goods;

use Request;
use Component\Member\Group\Util;

/**
 * 상품 혜택 관리 등록
 * @author <cjb3333@godo.co.kr>
 */
class GoodsBenefitRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {

        $getValue = Request::get()->toArray();

        // --- 메뉴 설정
        if (Request::get()->has('sno')) {
            $this->callMenu('goods', 'goods', 'benefitModify');
        } else {
            $this->callMenu('goods', 'goods', 'benefitRegister');
        }

        // --- 모듈 설정
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');

        try {

            $data = $goodsBenefit->getGoodsBenefit(gd_isset($getValue['sno']));
            $data['data']['icon'] = $goods->getManageGoodsIconInfo();

            // 할인금액 기준
            $fixedGoodsDiscount = Util::getFixedRateOptionData();
            unset($fixedGoodsDiscount['goods']);

            if($data['data']['benefitScheduleNextSno'] > 0 ){
                $nextBenefitData = $goodsBenefit->getGoodsBenefit($data['data']['benefitScheduleNextSno']);
            }

            // --- 관리자 디자인 템플릿
            if (isset($getValue['popupMode']) === true) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }

            // 회원그룹리스트
            $groupList = $memberGroup->getGroupListSelectBox(['key'=>'sno', 'value'=>'groupNm']);
            $this->setData('data', gd_htmlspecialchars($data['data']));
            $this->setData('nextBenefitData', gd_htmlspecialchars($nextBenefitData['data']));
            $this->setData('checked', $data['checked']);
            $this->setData('selected', $data['selected']);
            $this->setData('groupCnt', $groupList['cnt']);
            $this->setData('groupList', $groupList['data']);
            $this->setData('fixedGoodsDiscount', $fixedGoodsDiscount);
            $this->setData('exceptBenefit', Util::getExceptBenefitData());
            $this->setData('popupMode', gd_isset($getValue['popupMode']));

        } catch (\Exception $e) {
            throw $e;
        }

    }
}
