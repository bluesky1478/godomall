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

/**
 * 주문 리스트 설정 레이어 페이지
 * [관리자 모드] 주문 리스트 설정 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerOrderListConfigController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            // --- 주문 리스트 설정 config 불러오기
            $data = gd_policy('order.defaultSearch');
            gd_isset($data['searchPeriod'], 7);
            gd_isset($data['searchStatus'], array_keys($order->statusStandardNm));

            // --- 기본 상태 설정
            if ($data['searchStatus'] !== null) {
                $data['searchStatus'] = explode(STR_DIVISION, $data['searchStatus']);
                foreach ($data['searchStatus'] as $val) {
                    $checked['searchStatus'][$val] = 'checked="checked"';
                }
            }

            // --- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('statusStandardNm', $order->statusStandardNm);
            $this->setData('data', $data);
            $this->setData('checked', $checked);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
