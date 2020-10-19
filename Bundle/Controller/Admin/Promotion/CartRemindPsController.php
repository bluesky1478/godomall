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
namespace Bundle\Controller\Admin\Promotion;

use Component\CartRemind\CartRemind;
use Component\Sms\Exception\PasswordException;
use Framework\Debug\Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Message;
use Request;

class CartRemindPsController extends \Controller\Admin\Controller
{

    /**
     * 장바구니 알림 처리
     * [관리자 모드] 장바구니 알림 처리
     *
     * @author su
     */
    public function index()
    {
        try {
            $cartRemind = new CartRemind();
            switch (Request::post()->get('mode')) {
                case 'insertCartRemind':
                case 'modifyCartRemind':
                    // 모듈 호출
                    $postValue = Request::post()->toArray();
                    $cartRemind->setCartRemind($postValue);
                    $this->layer(__('저장이 완료되었습니다.'), 'parent.location.href="cart_remind_list.php";');
                    break;
                case 'modifyCartRemindMessage':
                    $postValue = Request::post()->toArray();
                    $cartRemind->setCartRemindSendMessage($postValue);
                    $this->layer(__('수정되었습니다.'), 'parent.location.href="cart_remind_list.php";');
                    break;
                case 'sendCartRemindManual':
                    $postValue = Request::post()->toArray();
                    try {
                        $result = $cartRemind->sendCartRemind($postValue);
                    } catch (PasswordException $e) {
                        $result['msg'] = $e->getMessage();
                    }
                    echo json_encode($result);
                    exit;
                    break;
                case 'setCartRemindAutoState':
                    $postValue = Request::post()->toArray();
                    $cartRemind->setCartRemindAutoState($postValue);
                    $result['code'] = 99;
                    $result['msg'] = __('변경되었습니다.');
                    echo json_encode($result);
                    exit;
                    break;
                case 'deleteCartRemind':
                    $postValue = Request::post()->toArray();
                    $cartRemind->deleteCartRemind($postValue);
                    $this->layer(__('삭제되었습니다.'), 'parent.location.href="cart_remind_list.php";');
                    break;
            }
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage()); //새로고침안됨
        }
    }
}
