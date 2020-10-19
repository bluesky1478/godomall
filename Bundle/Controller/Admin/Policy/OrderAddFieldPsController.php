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

use Component\Order\OrderAdmin;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Message;
use Request;
use Exception;
use App;

class OrderAddFieldPsController extends \Controller\Admin\Controller
{
    public function index()
    {

        // --- 각 배열을 trim 처리
        $postValue = Request::post()->toArray();
        $mallSno = gd_isset($postValue['mallSno'], 1);

        // --- 모듈 호출
        $orderAdmin = new OrderAdmin();

        // 각 모드에 따른 처리
        switch (Request::request()->get('mode')) {
            // --- 추가 / 수정
            case 'insertOrderAddField':
            case 'modifyOrderAddField':
                try {
                    $orderAdmin->setOrderAddField($postValue);
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.reload();');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            // --- 삭제
            case 'deleteOrderAddField':
                try {
                    $orderAdmin->setOrderAddFieldDelete($postValue['orderAddFieldNo'], $mallSno);
                    $this->layer(__('삭제 되었습니다.'), 'top.location.reload();');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            // --- 설정 저장
            case 'configOrderAddField':
                try {
                    // 추가정보 사용여부 설정
                    $orderAddFieldConfigArrData = [
                        'orderAddFieldUseFl' => $postValue['orderAddFieldUseFl'],
                    ];
                    gd_set_policy('order.addField', $orderAddFieldConfigArrData, true, $mallSno);

                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.reload();');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            // --- 순서 수정
            case 'changeOrderAddFieldSort':
                try {
                    // 추가정보 노출순서 저장
                    $orderAdmin->setOrderAddFieldSort($postValue['orderAddFieldSort'], $mallSno);
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.reload();');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            // --- 노출상태 수정
            case 'changeOrderAddFieldDisplay':
                try {
                    $orderAdmin->setOrderAddFieldDisplay($postValue['orderAddFieldNo'], $postValue['orderAddFieldDisplay'], $mallSno);
                    echo __('저장이 완료되었습니다.');
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                break;

            // --- 필수여부 수정
            case 'changeOrderAddFieldRequired':
                try {
                    $orderAdmin->setOrderAddFieldRequired($postValue['orderAddFieldNo'], $postValue['orderAddFieldRequired'], $mallSno);
                    echo __('저장이 완료되었습니다.');
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                break;
        }
    }
}
