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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Base;

use Component\Member\Manager;
use Component\Policy\MainSettingPolicy;
use Globals;

/**
 * Class LayerOrderSettingController
 *
 * @package Bundle\Controller\Admin\Base
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerOrderSettingController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        $orderMode = \Request::post()->get('mode', 'orderSetting');
        // 정책 가져오기
        $policy = new MainSettingPolicy();
        $managerSno = \Session::get(Manager::SESSION_MANAGER_LOGIN . '.sno');
        if ($orderMode === 'orderPresentation') {
            $data = $policy->getOrderPresentation($managerSno);
        } else {
            $data = $policy->getOrderMainSetting($managerSno);
        }
        $checked['period'][$data['period']] = 'checked';
        $checked['orderCountFl'][$data['orderCountFl']] = 'checked';
        $this->setData('checked', $checked);
        $this->setData('orderStatus', $data['orderStatus']);

        /** @var \Bundle\Component\Order\OrderAdmin $order 주문상태 전체 가져오기 (순서를 맞추기 위해) */
        $order = \App::load('\\Component\\Order\\OrderAdmin');
        $orderStatusList = $order->getOrderStatusAdmin();
        if(key_exists('b2', $orderStatusList)){
            $orderStatusList['b2'] = __('반품 반송중');
        }
        if(key_exists('e2', $orderStatusList)){
            $orderStatusList['e2'] = __('교환 반송중');
        }

        // 결제시도 제거
        unset($orderStatusList['f1']);

        // 공급사 로그인인 경우 입금대기 제거
        if (Manager::isProvider()) {
            unset($orderStatusList['o1']);
            unset($orderStatusList['f2']);
            unset($orderStatusList['f3']);
            unset($orderStatusList['c1']);
            unset($orderStatusList['c2']);
            unset($orderStatusList['c3']);
            unset($orderStatusList['c4']);
        } else {
            $plusShopData = Globals::get('gPlusShop');
            if ($plusShopData['cGodo_UserExchange']['appUseFl'] == 'y') {
                $orderStatusList['er'] = __('고객교환신청');
                $orderStatusList['br'] = __('고객반품신청');
                $orderStatusList['rr'] = __('고객환불신청');
            }
        }

        $this->setData('mode', $orderMode);
        $this->setData('statusSearchableRange', $orderStatusList);

        // 템플릿 정의
        $this->getView()->setDefine('layout', 'layout_layer.php');

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('base/layer_order_setting.php');
    }
}
