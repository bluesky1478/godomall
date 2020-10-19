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

namespace Bundle\Controller\Front\Mypage;

use Component\Database\DBTableField;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Exception;
use Framework\Debug\Exception\AlertReloadException;
use Request;
use Session;

/**
 * Class MypageOrderSettleController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  YeonKyungKim <kyeonk@godo.co.kr>
 */
class LayerOrderSettleController extends \Controller\Front\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $reqValue = Request::post()->toArray();

            // 모듈 설정
            $order = \App::load('\\Component\\Order\\Order');

            $this->setData('eachOrderStatus', $order->getEachOrderStatus(\Session::get('member.memNo'), null, 30));

            // 주문 리스트 정보
            $orderData = $order->getOrderView($reqValue['orderNo']);

            //반품가능한 상품들은 배송중, 배송완료인 상품만 표시
            $orderData = $order->getOrderClaimList($orderData, 'backRegist');

            if ($orderData['orderChannelFl'] == 'naverpay') {
                throw new \Exception(__('잘못된 경로로 접근하셨습니다.'));
            }

            // 상품 옵션가 표시설정 config 불러오기
            $optionPriceConf = gd_policy('goods.display');

            $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정
            $this->setData('orderData', $orderData);
            $this->setData('orderNo', $reqValue['orderNo']);

        } catch (Exception $e) {
            throw new AlertReloadException(__($e->getMessage()));

        }
    }
}