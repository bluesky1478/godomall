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
namespace Bundle\Controller\Front\Share;


use Component\Board\Board;
use Component\Member\Util\MemberUtil;
use Component\Goods\AddGoodsAdmin;
use Component\Goods\Goods;
use Component\Order\OrderAdmin;
use Framework\Utility\SkinUtils;

class LayerOrderSearchController extends \Controller\Front\Controller
{
    public function index()
    {
        $order = new OrderAdmin();
        $getValue = \Request::get()->toArray();
        $outPut = parse_url(\Request::getReferer());
        parse_str($outPut['query'],$query);
        $bdId = $query['bdId'];
        if($bdId == Board::BASIC_GOODS_REIVEW_ID){
            if(\Session::get('guest.orderNo')){
                $getValue['orderNo'] =  \Session::get('guest.orderNo');
            }
        }
        else{
            $getValue['exceptOrderStatus'] = ['o1'];
        }

        $getValue['treatDateFl'] = 'og.regDt';
        $getValue['pageNum'] = 10;
        $getValue['memNo']  = MemberUtil::isLogin() ? \Session::get('member.memNo') : 0;
        $getValue['view'] = 'orderGoods';
        $getData = $order->getOrderListForAdmin($getValue, 7);
        unset($getData['data']);
        foreach ($getData as $key => $val) {
            if (is_numeric($key) === false) {
                continue;
            }
            $orderGoodsData[] = $val;
        }

        $goods = new Goods();
        $addGoods = new AddGoodsAdmin();
        foreach ($orderGoodsData as &$val) {
            $tmpOption = null;
            $goodsImage = $goods->getGoodsImage($val['goodsNo'], 'main');
            if($val['goodsType'] == 'addGoods') {
                $addGoodsData = $addGoods->getDataAddGoods($val['goodsNo'])['data'];
                $val['goodsImageSrc'] = SkinUtils::imageViewStorageConfig($val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 100, 'add_goods')[0];
                $val['totalGoodsPrice'] = $val['goodsCnt'] * ($addGoodsData['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액

            }
            else {
                $goodsData = $goods->getGoodsInfo($val['goodsNo']);
                $val['goodsImageSrc'] = SkinUtils::imageViewStorageConfig($goodsImage[0]['imageName'], $val['imagePath'], $val['imageStorage'], 100, 'goods')[0];
                $val['totalGoodsPrice'] = $val['goodsCnt'] * ($goodsData['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
            }

            if ($val['optionInfo']) {
                $optionInfo = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo'], true));
                foreach ($optionInfo as $option) {
                    $tmpOption[] = $option[0] . ':' . $option[1];
                }
            }

            if (empty($val['optionTextInfo']) === false) {
                $optionTextInfo = json_decode(gd_htmlspecialchars_stripslashes($val['optionTextInfo'], true));
                foreach ($optionTextInfo as $option) {
                    $tmpOption[] = $option[0] . ':' . $option[1];
                }
            }

            $val['optionName'] = implode('<br>',$tmpOption);
            $val['orderStatusText'] = $order->getOrderStatusAdmin($val['orderStatus']) ;
        }
        // 페이지 설정
        $page = \App::load('Component\\Page\\Page');
        $pagination = $page->getPage('goAjaxPaging(\'PAGELINK\')');
        $this->setData('pagination', gd_isset($pagination));
        $this->setData('total', $page->getTotal());
        $this->setData('data', gd_isset($orderGoodsData));
//        $this->setData('cfg', gd_isset($orderGoodsData));
    }
}
