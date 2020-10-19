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
use Cookie;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;
use Session;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  <bumyul2000@godo.co.kr>
 */
class LayerOrderDeliveryMethodController extends \Controller\Front\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 모듈 설정
            $order = \App::load('\\Component\\Order\\Order');

            $deliveryVisitAddress = '';

            // 주문 상품 정보
            $orderGoodsData = $order->getOrderGoodsData(Request::post()->get('orderNo'), Request::post()->get('orderGoodsNo'), null, null, 'user', false);

            if($orderGoodsData['deliveryMethodFl'] == 'visit'){
                // 배송비조건 상세내용
                $delivery = \App::load('\\Component\\Delivery\\Delivery');
                $deliveryData = $delivery->getDataSnoDelivery($orderGoodsData['deliverySno']);
                if (empty($orderGoodsData['visitAddress']) === false) {
                    $deliveryVisitAddress = $orderGoodsData['visitAddress'];
                } else {
                    $deliveryVisitAddress = '(' . $deliveryData['basic']['dmVisitTypeZonecode'] . ') ' . $deliveryData['basic']['dmVisitTypeAddress'] . ' ' . $deliveryData['basic']['dmVisitTypeAddressSub'];
                }
            }

            $this->setData('orderGoodsData', $orderGoodsData);
            $this->setData('deliveryVisitAddress', $deliveryVisitAddress);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
