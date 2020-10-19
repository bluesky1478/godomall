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
namespace Bundle\Controller\Mobile\Mypage;

use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;
use Exception;
use Message;
use Request;
use Session;
/**
 * 상품 보관함 처리 페이지
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
class WishListPsController extends \Controller\Mobile\Controller
{

    /**
     * 템플릿 처리
     *
     * @throws Except
     */
    public function index()
    {
        // --- 상품 보관함 class
        $wish = \App::Load(\Component\Wish\Wish::class);

        // _POST
        $postValue = Request::post()->toArray();

        // --- 각 모드별 처리
        switch (Request::request()->get('mode')) {

            // 상품 보관함 추가
            case 'wishModify':
            case 'wishIn':
                // --- 상품 보관함 class

                if(Session::has('member')) {
                    try {
                        // 장바구니 추가
                        $wish->saveInfoWish($postValue);
                        // 처리별 이동 경로
                        $returnUrl = './wish_list.php';

                        $this->redirect($returnUrl, null, 'parent');
                    } catch (Exception $e) {
                        throw new AlertOnlyException($e->ectMessage);
                    }
                } else {
                    throw new AlertBackException(__('로그인하셔야 해당 서비스를 이용하실 수 있습니다.'));
                }
                break;

            // 장바구니 담기
            case 'wishToCart':
                // --- 상품 보관함 class
                try {

                    $wish->setWishToCart(gd_isset($postValue['sno']));

                    throw new AlertRedirectException(null,null, null, '../order/cart.php', 'parent');

                } catch (Exception $e) {
                    throw $e;
                }

                break;

            // 상품 삭제
            case 'delete':
                try {

                    $wish->setWishDelete($postValue['sno']);



                } catch (Exception $e) {
                    throw $e;
                }

                break;

        }

        exit();

    }
}
