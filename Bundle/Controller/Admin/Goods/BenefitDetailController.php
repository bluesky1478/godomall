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

use Component\Member\Group\Util;
use Exception;
use Request;
use Globals;

/**
 * Class ShortUrlListController
 *
 * @package Bundle\Controller\Admin\Goods
 * @author  Young-jin Bag <kookoo135@godo.co.kr>
 */
class BenefitDetailController extends \Controller\Admin\Controller
{
    public function index()
    {
        $goodsNo = Request::get()->get('goodsNo');
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');

        $conf['mileage'] = Globals::get('gSite.member.mileageGive'); // 마일리지 지급 여부
        $conf['mileageBasic']['mileageText'] = '판매가';
        if ($conf['mileageBasic']['optionPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+옵션가';
        if ($conf['mileageBasic']['addGoodsPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+추가상품가';
        if ($conf['mileageBasic']['textOptionPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+텍스트옵션가';
        if ($conf['mileageBasic']['goodsDcPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+상품할인가';
        if ($conf['mileageBasic']['addGoodsPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+회원할인가';
        if ($conf['mileageBasic']['couponDcPrice'] == 1) $conf['mileageBasic']['mileageText'] .= '+쿠폰할인가';

        // 지급마일리지 설정
        gd_isset($conf['mileage']['giveType'], 'price'); // 지급 기준
        if ($conf['mileage']['giveType'] == 'price') { // 구매금액의 %
            gd_isset($conf['mileage']['goods'], 0); // %
        } else if ($conf['mileage']['giveType'] == 'priceUnit') { // 구매금액당 지급
            gd_isset($conf['mileage']['goodsPriceUnit'], 0); // 구매금액당
            gd_isset($conf['mileage']['goodsMileage'], 0); // 원 지급
        } else if ($conf['mileage']['giveType'] == 'cntUnit') { // 수량(개)당 지급
            gd_isset($conf['mileage']['cntMileage'], 0); // 원 지급
        }

        $data = $goods->getDataGoods($goodsNo);

        //상품 혜택 사용시에 해당 데이터 재설정
        if($data['data']['goodsBenefitSetFl'] == 'y'){
            $data['data'] = $goodsBenefit->goodsDataReset($data['data'], 'image');
        }

        // 회원그룹리스트
        $groupList = $memberGroup->getGroupListSelectBox(['key'=>'sno', 'value'=>'groupNm']);
        $fixedGoodsDiscount = Util::getFixedRateOptionData();

        $fixedGoodsDiscountData[] = '판매가';
        if (empty($data['data']['fixedGoodsDiscount']) === false) {
            foreach ($data['data']['fixedGoodsDiscount'] as $val) {
                $fixedGoodsDiscountData[] = $fixedGoodsDiscount[$val];
            }
        }
        $data['data']['fixedGoodsDiscount'] = $fixedGoodsDiscountData;

        if (empty($data['data']['exceptBenefit']) === false) {
            $exceptBenefitData = [];
            foreach ($data['data']['exceptBenefit'] as $val) {
                $exceptBenefitData[] = Util::getExceptBenefitData()[$val];
            }
            $data['data']['exceptBenefit'] = $exceptBenefitData;
        }

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('conf', $conf);
        $this->setData('data', $data['data']);
        $this->setData('groupList', $groupList['data']);
        $this->setData('fixedGoodsDiscount', $fixedGoodsDiscount);
        $this->setData('exceptBenefit', Util::getExceptBenefitData());
        $this->setData('arrMileageFl', ['c'=>'통합설정', 'g'=>'개별설정']);
        $this->setData('arrMileageGroup', ['all'=>'전체회원', 'group'=>'특정회원등급']);
        $this->setData('arrGoodsDiscountGroup', ['all'=>'전체(회원+비회원)', 'member'=>'회원전용(비회원제외)', 'group'=>'특정회원등급']);
    }
}
