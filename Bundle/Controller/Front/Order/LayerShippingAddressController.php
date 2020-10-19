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

use Component\Member\Util\MemberUtil;
use App;
use Session;
use Request;
use Exception;

/**
 * Class LayerDeliveryAddress
 *
 * @package Bundle\Controller\Front\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerShippingAddressController extends \Controller\Front\Controller
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

            if (!MemberUtil::isLogin()) {
                $this->js("alert('" . __('로그인을 하셔야 이용하실 수 있습니다.') . "'); top.location.href = '../member/login.php';");
            }

            $page = Request::get()->get('page', 1);
            $order = App::load(\Component\Order\Order::class);

            // 배송지 관리 리스트
            $deliveryAddress = $order->getShippingAddressList($page);
            $this->setData('deliveryAddress', $deliveryAddress);
            $this->setData('shippingNo', \Request::get()->get('shippingNo'));

            // 페이징 처리
            $pager = App::load(\Component\Page\Page::class);
            $this->setData('pagination', $pager->getPage('goPageOnDeliveryAddress(\'PAGELINK\');'));

        } catch (Exception $e) {
            $this->json([
                'error' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
