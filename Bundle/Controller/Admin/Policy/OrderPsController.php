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

use Message;
use Exception;
use Request;

/**
 * 주문 정책 저장 처리 페이지
 * [관리자 모드] 주문 정책 저장 처리 페이지
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class OrderPsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function index()
    {
        $postValue = Request::post()->toArray();
        $getValue = Request::get()->toArray();

        switch (Request::request()->get('mode')) {
            // --- 주문 기본설정 저장
            case 'updateOrderBasic':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveOrderBasic($postValue);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- 장바구니 설정 저장
            case 'updateOrderCart':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveOrderCart($postValue);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- 상품 보관함 설정 저장
            case 'order_wish':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveOrderWish($postValue);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- 주문 상태 설정 저장
            case 'updateOrderStatus':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveOrderStatus($postValue['orderStep']);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;
            // --- 해외몰 주문 상태 설정 저장
            case 'updateOrderStatusGlobal':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    foreach ($postValue['orderStep'] as $key => $value) {
                        $policy->saveOrderStatus($value, $key);
                    }

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            case 'checkOrderStatus':
                try {
                    $order = \App::load('\\Component\\Order\\OrderAdmin');
                    // 해당 주문상태를 가진 주문이 있는 체크
                    if ($order->checkUsableOrderStatus(Request::post()->get('orderStatus'))) {
                        $this->json([
                            'error' => 1,
                            'message' => _('현재 사용 중인 주문상태는 삭제하실 수 없습니다. 주문리스트에서 해당 주문의 상태를 변경 후 삭제하시기 바랍니다.'),
                        ]);
                    } else {
                        $this->json([
                            'error' => 0,
                            'message' => 'OK',
                        ]);
                    }
                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }
                break;

                // 거래명세서/ 주문내역서 출력 설정
            case 'updateOrderPrint' :
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveOrderPrintConfig($postValue);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;
        }
    }
}
