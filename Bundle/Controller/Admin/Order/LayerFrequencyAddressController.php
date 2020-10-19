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

use Framework\Utility\ArrayUtils;
use Globals;
use App;
use Request;
use Exception;

/**
 * 주문 상세 페이지
 * [관리자 모드] 주문 상세 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerFrequencyAddressController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 요청 정보
            $getValue = Request::get()->all();

            // 주문 정보
            $order = App::load(\Component\Order\OrderAdmin::class);

            // 자주 쓰는 주소 가져오기
            $getValue['searchMode'] = 'n';

            $getData = $order->getFrequencyAddress($getValue);
            $this->setData('data', $getData['data']);
            $this->setData('checked', $getData['checked']);
            $this->setData('search', $getData['search']);
            $this->setData('page', $getData['page']);

            // --- 템플릿 정의
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
