<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Order;

use Component\Order\LogisticsOrder;
use Component\Policy\Policy;
use Framework\Debug\Exception\LayerException;
use League\Flysystem\Exception;

/**
 * Class CJ대한통운 연동안내
 * @package Bundle\Controller\Admin\Order
 * @author  Lee Namju <lnjts@godo.co.kr>
 */
class LogisticsPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $req = \Request::post()->all();
        $mode = \Request::request()->get('mode');
        try {
            $logisticsOrder = new LogisticsOrder();
            $logger = \Logger::channel('logistics');
            switch ($mode){
                case 'config' :
                    $data = $req;
                    $data['SENDR_ADDR'] = trim($data['address']);
                    $data['SENDR_DETAIL_ADDR'] = trim($data['addressSub']);
                    $data['SENDR_ZIP_NO'] = $data['zipcode'];
                    $data['BOX_TYPE_CD'] = "01";
                    unset($data['address']);
                    unset($data['addressSub']);
                    unset($data['zipcode']);
                    $result = $logisticsOrder->sendConfigSaveApi($data);
                    if($result == 'ok') {
                        $logisticsOrder->saveLogisticsPolicy($data);
                        $this->layer(__('저장이 완료되었습니다.'), 'top.location.reload();');
                    }
                    else {
                        throw new Exception('저장이 실패되었습니다.');
                    }
                    break;
                case 'check' :
                    $result = $logisticsOrder->checkCustIdApi($req['custId']);
                    exit($result);
                    break;
                case 'reservation' :    //예약하기
                    $logisticsOrder->batchReservation($req['statusCheck'], $req['viewType']);
                    $this->layer(__('택배 예약이 완료되었습니다.'), 'top.location.reload();');
                    break;
                case 'cancel' :
                    /*if(\Request::get()->get('viewType') == 'order') {
                        $logisticsOrder->cancelReservationByOrderInfoSno(\Request::get()->get('orderInfoSno'));
                    }
                    else {
                        $logisticsOrder->cancelReservationByOrderGoodsNo(\Request::get()->get('sno'));
                    }*/
                    $logisticsOrder->cancelReservationByMpckKey(\Request::get()->get('mpckKey'));
                    $this->layer(__('택배 취소가 완료되었습니다.'), 'top.location.reload();');
                    break;
            }

        } catch (\Throwable $e) {
            $logger->info(__METHOD__,[$req, $e->getMessage()]);
            throw new LayerException($e->getMessage(),0,null,null,100000);
        }
    }
}
