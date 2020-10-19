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

namespace Bundle\Controller\Admin\Policy;

use Request;

/**
 * Class DeliveryLayerChargeController
 *
 * @package Controller\Admin\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerDeliveryChargeController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        //--- 모듈 호출
        $delivery = \App::load('\\Component\\Delivery\\Delivery');
        $getData = $delivery->getChargeDeliveryDetail(Request::get()->get('sno'));
        $charge = $getData['multiCharge'] ?? $getData['charge'];

        $getData['basic']['deliveryMethodFlData'] = [];
        $deliveryMethodFlArr = array_values(array_filter(explode(STR_DIVISION, $getData['basic']['deliveryMethodFl'])));
        if($deliveryMethodFlArr > 0){
            foreach($deliveryMethodFlArr as $key => $value){
                if($value === 'etc'){
                    $deliveryMethodListName = gd_get_delivery_method_etc_name();
                }
                else {
                    $deliveryMethodListName = $delivery->deliveryMethodList['name'][$value];
                }
                $getData['basic']['deliveryMethodFlData'][$value] = $deliveryMethodListName;
                $getData['basic']['deliveryMethodFlKey'][$value] = $key;
            }
        }
        $getData['multipleDeliveryFl'] = false;
        if ($getData['basic']['fixFl'] != 'free' && $getData['basic']['deliveryConfigType'] == 'etc' && (count($getData['basic']['deliveryMethodFlData']) <= 1) === false) {
            $getData['multipleDeliveryFl'] = true;
        }

        $lastKey = gd_array_last_key($charge);
        foreach ($charge as $key => $val) {
            if ($getData['multipleDeliveryFl'] === true) {
                foreach ($val as $k => $v) {
                    $charge[$key][$k] = $this->setCharge($v, $getData['basic']['fixFl'], $key, $lastKey);
                }
            } else {
                $charge[$key] = $this->setCharge($val, $getData['basic']['fixFl'], $key, $lastKey);
            }
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');

        // 공급사 페이지 설정
        $this->getView()->setPageName('policy/layer_delivery_charge.php');

        $this->setData('sno', Request::get()->get('sno'));
        $this->setData('fixText', $delivery->getFixFlText($getData['basic']['fixFl']));
        $this->setData('taxFreeStr', $getData['basic']['taxFreeFl'] == 't' ? __('과세') . ' ' . $getData['basic']['taxPercent'] . '%' : __('면세') . ' ');
        $this->setData('multipleDeliveryFl', $getData['multipleDeliveryFl']);
        $this->setData('basic', $getData['basic']);
        $this->setData('data', $charge);
    }

    public function setCharge($charge, $fixFl, $key, $lastKey)
    {
        // 배송비유형에 따른 단위 금액 소수점 제거 처리
        switch ($fixFl) {
            case 'count':
                $charge['unitStart'] = intval($charge['unitStart']);
                $charge['unitEnd'] = intval($charge['unitEnd']);
                break;
            case 'price':
                $charge['unitStart'] = gd_money_format($charge['unitStart']);
                $charge['unitEnd'] = gd_money_format($charge['unitEnd']);
                break;
            default:
                $charge['unitStart'] = number_format($charge['unitStart'], 2);
                $charge['unitEnd'] = number_format($charge['unitEnd'], 2);
                break;
        }

        if ($lastKey === $key) {
            $charge['condition'] = $charge['unitStart'] . $charge['unitText'] . ' ' . __('이상');
        } else {
            $charge['condition'] = $charge['unitStart'] . ' ~ ' . $charge['unitEnd'] . $charge['unitText'];
        }

        return $charge;
    }
}
