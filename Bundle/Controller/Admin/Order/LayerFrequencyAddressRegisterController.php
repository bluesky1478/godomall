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

use Framework\Debug\Exception\LayerNotReloadException;
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
class LayerFrequencyAddressRegisterController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 주문 정보
            $order = App::load(\Component\Order\OrderAdmin::class);

            // sno
            if (Request::get()->has('sno')) {
                $this->setData('data', $order->getFrequencyAddressView(Request::get()->get('sno')));
            }

            // 그룹정보
            $this->setData('groups', $order->getFrequencyAddressGroup());

            // 메일도메인
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);

            // --- 템플릿 정의
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅

        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
    }
}
