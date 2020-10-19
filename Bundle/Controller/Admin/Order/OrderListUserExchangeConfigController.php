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

use Component\Page\Page;
use Component\Payment\CashReceipt;
use Exception;

/**
 * 현금영수증 신청 리스트 페이지
 * @author Seong Hoyun <seonghu@godo.co.kr>
 */
class OrderListUserExchangeConfigController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'cancel', 'userExchangeConfig');

        // --- 설정 불러오기
        $config = gd_policy('order.userExchangeConfig');
        $paycoPolicy = gd_policy('pg.payco');
        $kakaoPolicy = gd_policy('pg.kakaopay');
        if(!empty($paycoPolicy) && $paycoPolicy['useType'] != 'N' && $paycoPolicy['testYn'] != 'Y'){ //페이코 사용 할 경우
            $paycoUse = true;
            $this->setData('paycoAutoCancelable', $paycoUse);
        }
        if(!empty($kakaoPolicy) && $kakaoPolicy['testYn'] != 'Y'){ //카카오페이 사용 여부
            $kakaoUse = true;
            $this->setData('kakaoAutoCancelable', $kakaoUse);
        }

        // --- SET Default Setting
        if(empty($config['orderListUserExchangeConfigUse'])){
            $config['orderListUserExchangeConfigUse'] = 'n';
            $config['settleType'][] = 'c';
            $config['settleType'][] = 'p';
            $config['settleType'][] = 'k';
        }
        if(empty($config['orderListUserExchangeConfigOrderStatus'])){
            $config['orderListUserExchangeConfigOrderStatus'] = 'unlimited';
        }
        if(empty($config['orderListUserExchangeConfigReStock'])){
            $config['orderListUserExchangeConfigReStock'] = 'n';
        }
        if(empty($config['orderListUserExchangeConfigReCoupon'])){
            $config['orderListUserExchangeConfigReCoupon'] = 'n';
        }

        // --- RADIO checked
        $checked['orderListUserExchangeConfigUse'][$config['orderListUserExchangeConfigUse']] = 'checked="checked"';
        $checked['orderListUserExchangeConfigOrderStatus'][$config['orderListUserExchangeConfigOrderStatus']] = 'checked="checked"';
        $checked['orderListUserExchangeConfigReStock'][$config['orderListUserExchangeConfigReStock']] = 'checked="checked"';
        $checked['orderListUserExchangeConfigReCoupon'][$config['orderListUserExchangeConfigReCoupon']] = 'checked="checked"';

        // --- CHECKBOX checked
        $checked['orderListUserExchangeConfigP'][$config['orderListUserExchangeConfigP']] = 'checked="checked"';
        $checked['orderListUserExchangeConfigG'][$config['orderListUserExchangeConfigG']] = 'checked="checked"';
        foreach ($config['settleType'] as $value) {
            $checked['settleType'][$value] = 'checked="checked"';
        }

        //주문상태 설정 불러오기
        $orderStep = gd_policy('order.status');
        foreach($orderStep as $key1 => $value1){
            if(in_array($key1, ['payment', 'goods', 'delivery'])){
                foreach($value1 as $key2 => $value2){
                    if(!in_array($key2, ['cplus', 'mplus', 'sminus'])){
                        if($value2['useFl'] == 'y'){
                            $orderStepFilter[$key1][] = $value2['admin'];
                        }
                    }
                }
            }
        }
        foreach($orderStepFilter as $key => $value){
            $orderStep[$key] = implode(', ', $value);
        }

        // --- 관리자 디자인 템플릿
        $this->setData('config', $config);
        $this->setData('orderStep', $orderStep);
        $this->setData('checked', $checked);

    }
}
