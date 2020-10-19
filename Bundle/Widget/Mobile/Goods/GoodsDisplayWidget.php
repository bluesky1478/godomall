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
namespace Bundle\Widget\Mobile\Goods;

use Request;

/**
 * Class GoodsDisplayWidget
 *
 * @package Bundle\Widget\Front\Goods
 * @author  Young Eun Jung <atomyang@godo.co.kr>
 */
class GoodsDisplayWidget extends \Widget\Mobile\Widget
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        if (is_null($this->getData('soldoutDisplay'))) {
            $this->setData('soldoutDisplay', gd_policy('soldout.mobile'));
        }

        if (is_null($this->getData('widgetTheme'))) {
            $widgetTheme = [
                'lineCnt' => '4',
                'iconFl' => 'y',
                'soldOutIconFl' => 'y',
                'displayField' => [
                    'brandCd',
                    'makerNm',
                    'goodsNm',
                    'fixedPrice',
                    'goodsPrice',
                    'coupon',
                    'mileage',
                    'shortDescription',
                ],
            ];
            $this->setData('widgetTheme', gd_isset($widgetTheme));
        }
        else{
            $this->setData('typeClass',$this->getData('widgetTheme')['displayType']);

        }

        if (is_null($this->getData('typeClass'))) {
            $this->setData('typeClass', '01');
        }

        $goodsList = $this->getData('widgetGoodsList');
        $cateInfo = $this->getData('widgetTheme');
        // 카테고리 노출항목 중 상품할인가
        $goods = \App::load('\\Component\\Goods\\Goods');
        if (in_array('goodsDcPrice', $cateInfo['displayField'])) {
            foreach ($goodsList as $key => $val) {
                foreach ($val as $key2 => $val2) {
                    $goodsList[$key][$key2]['goodsDcPrice'] = $goods->getGoodsDcPrice($val2);
                }
            }
        }

        // 장바구니 설정
        if ($this->getData('typeClass') == '11') {
            $cartInfo = gd_policy('order.cart');
            $this->setData('cartInfo', gd_isset($cartInfo));
        }

        // 배송비 유형 설정
//        $delivery = \App::load('\\Component\\Delivery\\Delivery');
//        foreach($goodsList as $key => $goodInfo) {
//            foreach($goodInfo as $k => $v) {
//                $goodsView = $goods->getGoodsView($v['goodsNo']);
//                $deliveryInfo = $delivery->getDeliveryType($goodsView['deliverySno']);
//                $goodsList[$key][$k]['deliveryType'] = $deliveryInfo['deliveryType'];
//                $goodsList[$key][$k]['deliveryMethod'] = $deliveryInfo['method'];
//                $goodsList[$key][$k]['deliveryDes'] = $deliveryInfo['description'];
//            }
//        }

        $this->setData('goodsList', $goodsList);
        $this->setData('themeInfo', $cateInfo);
        $this->setData('mainData', ['sno'=>'widget']);
        $this->getView()->setDefine('goodsTemplate', 'goods/list/list_' . $this->getData('typeClass') . '.html');
    }
}
