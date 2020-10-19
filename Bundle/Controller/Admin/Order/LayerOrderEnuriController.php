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

use Request;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;

/**
 * 운영자 추가 할인 레이어 페이지
 * [관리자 모드] 운영자 추가 할인 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  by
 */
class LayerOrderEnuriController extends \Controller\Admin\Controller
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
            $data = $order->getOrderView(Request::request()->get('orderNo'), null, null, 'o');
            $goods = $data['goods'];
            unset($data['goods'], $data['cnt']);

            foreach ($goods as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        if(substr($val['orderStatus'], 0, 1) !== 'o'){
                            continue;
                        }

                        $data['goods'][$sKey][$dKey][] = $val;

                        // 테이블 UI 표현을 위한 변수
                        $addGoodsCnt = $val['addGoodsCnt'];
                        $data['cnt']['scm'][$sKey] += 1 + $addGoodsCnt;
                    }
                }
            }

            if(count($data['goods']) < 1){
                throw new LayerNotReloadException("입금전 상태의 주문상품건이 존재하지 않습니다.");
            }
            if($data['orderChannelFl'] !== 'shop'){
                throw new LayerNotReloadException("네이버페이, 페이코 주문건은 운영자 추가 할인을 설정할 수 없습니다.");
            }

            $this->setData('data', $data);
            $this->setData('orderNo', Request::request()->get('orderNo'));

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
