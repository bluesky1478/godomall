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
namespace Bundle\Controller\Admin\Order;

use Exception;
use Request;

/**
 * Class LayerUserMemoController
 * 고객 신청 메모
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerSuperAdminMemoController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // POST 리퀘스트
            $postValue = Request::post()->toArray();
            $this->setData('orderNo', $postValue['orderNo']);
            $this->setData('regDt', $postValue['regDt']);

            // 모듈 설정
            $order = \App::load('\\Component\\Order\\Order');

            // 상품과 관련된 모든 데이터 가져오기
            $getData = $order->getOrderData($postValue['orderNo']);
            $this->setData('data', $getData);

            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/layer_super_admin_memo.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
