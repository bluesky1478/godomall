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

use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\AlertRedirectException;
use Message;
use Request;

/**
 * 우체국택배연동 처리 페이지
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class PostPsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        $postValue = Request::post()->toArray();

        $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');

        try {
            switch (Request::request()->get('mode')) {
                // 우체국택배 송장 발급
                case 'issue':

                        $result = $orderAdmin->saveOrderInvoice($postValue);

                        if(count($result) > 0 ) {
                            throw new LayerException(sprintf(__("필수 정보(수령자명,수령자 핸드폰번호,수령자 주소,수령자 우편번호,주문자명) 가 누락된 주문(%s) 외 우체국 택배 송장번호가 발급되었습니다."), implode(",",$result)),null,null,null,6000);
                        } else {
                            throw new LayerException("우체국 택배 송장번호가 발급되었습니다.");
                        }


                    break;
                // 우체국택배 예약
                case 'reserve':
                        $orderAdmin->reserveOrderInvoice($postValue);
                        throw new LayerException(__("우체국택배로 예약되었습니다."));
                    break;
                // 우체국택배 취소
                case 'cancel':
                    $orderAdmin->cancelOrderInvoice($postValue);
                    throw new LayerException(__("송장발급이 취소 되었습니다."));
                    break;
                // 우체국수동업데이트
                case 'manual':
                    $godoPost = \App::load('\\Component\\Godo\\GodoPostServerApi');
                    $data = $godoPost->saveManualUpdate();
                    throw new LayerException(sprintf(__("주문 %s 건의 우체국 택배 배송상태를 수동 업데이트 하였습니다."), $data));
                    break;
            }
        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }

        exit();
    }
}
