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
namespace Bundle\Controller\Admin\Goods;


use Component\Storage\Storage;
use Framework\Debug\Exception\LayerException;
use Exception;
use Request;

class PurchasePsController extends \Controller\Admin\Controller
{

    /**
     * 상품 관련 처리 페이지
     * [관리자 모드] 상품 관련 처리 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws Except
     * @throws LayerException
     * @param array $get
     * @param array $post
     * @param array $files
     */
    public function index()
    {

        // --- 각 배열을 trim 처리
        $postValue = Request::post()->toArray();

        // --- 매입처 class
        $purchase = \App::load('\\Component\\Goods\\Purchase');

        try {

            switch ($postValue['mode']) {
                // 매입처 등록 수정
                case 'register':
                case 'modify':
                    $purchase->saveInfoPurchase($postValue);
                    $this->layer(__('저장이 완료되었습니다.'));
                    break;
                // 매입처 삭제로 상태 변경(매입처는 완전 삭제 없음)
                case 'delete_state':
                    if (empty($postValue['purchaseNo']) === false) {
                        $purchase->setDelStatePurchase($postValue['purchaseNo']);
                        unset($postArray);
                        $this->layer(__('삭제 되었습니다.'));
                    }
            }

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }
}
