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

namespace Bundle\Controller\Front\Order;

use App;
use Session;
use Request;
use Exception;

/**
 * Class LayerShippingPsController
 *
 * @package Bundle\Controller\Front\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerShippingPsController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            if (!Request::isAjax()) {
                throw new Exception('Ajax ' . __('전용 페이지 입니다.'));
            }

            switch(Request::post()->get('mode')) {
                // 등록 액션
                case 'shipping_regist':
                case 'shipping_modify':
                    if (!Request::post()->has('sno') && Request::post()->get('mode') == 'shipping_modify') {
                        throw new Exception(__('배송지 관리 번호를 입력하세요.'));
                    }

                    $order = App::load(\Component\Order\Order::class);
                    if (!$order->registShippingAddress(Request::post()->toArray())) {
                        throw new Exception(__('이미 등록된 배송지 입니다.'));
                    }

                    $this->json([
                        'code' => 200,
                        'message' => __('배송지가 정상적으로 등록되었습니다.'),
                    ]);

                    break;

                // 삭제 액션
                case 'shipping_delete':
                    if (!Request::post()->has('sno')) {
                        throw new Exception(__('배송지 관리 번호를 입력하세요.'));
                    }

                    $order = App::load(\Component\Order\Order::class);
                    $order->deleteShippingAddress(Request::post()->get('sno'));

                    $this->json([
                        'code' => 200,
                        'message' => __('배송지가 정상적으로 삭제되었습니다.'),
                    ]);

                    break;
            }

            exit;

        } catch (Exception $e) {
            $this->json([
                'code' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
