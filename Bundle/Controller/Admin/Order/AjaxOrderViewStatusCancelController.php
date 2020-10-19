<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Order;

use App;
use Exception;
use Request;
use Globals;

/**
 * Class AjaxOrderViewStatusController
 *
 * @package Bundle\Controller\Admin\Order
 * @author su
 */
class AjaxOrderViewStatusCancelController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            $postValue = Request::post()->toArray();

            if ($postValue['mode'] == 'get_select_order_goods_cancel_data') {
                $orderGoodsCancel = [];
                foreach ($postValue['orderGoodsCancelSno'] as $key => $val) {
                    $orderGoodsCancel[$val] = $postValue['orderGoodsCancelCnt'][$key];
                }
                // 취소할 상품의 주문 정보
                $orderReOrderCalculation = App::load(\Component\Order\ReOrderCalculation::class);
                $cancelData = $orderReOrderCalculation->getSelectOrderGoodsCancelData($postValue['orderNo'], $orderGoodsCancel);
                $this->setData('cancelData', $cancelData);

                // 마일리지 기본 설정 - 마일리지 표시 설정
                $mileageUse = gd_policy('member.mileageBasic');
                $this->setData('mileageUse', gd_isset($mileageUse));
                // 예치금 기본 설정 - 예치금 표시 설정
                $depositUse = Globals::get('gSite.member.depositConfig');
                $this->setData('depositUse', gd_isset($depositUse));

                // --- 템플릿 정의
                $this->getView()->setDefine('layout', 'layout_layer.php');
            } else {

            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
