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
namespace Bundle\Controller\Admin\Provider\Goods;

use Framework\Debug\Exception\LayerException;
use Message;
use Request;

class AddGoodsPsController extends \Controller\Admin\Goods\AddGoodsPsController
{

    /**
     * 추가상품  처리 페이지
     * [관리자 모드] 추가상품  관련 처리 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @throws Except
     * @throws LayerException
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        parent::index();

        $postValue = Request::post()->toArray();

        // --- 상품노출 class
        $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');

        try {

            switch ($postValue['mode']) {
                case 'applyWithdraw':

                    if (empty($postValue['addGoodsNo']) === false) {

                        $addGoods->setApplyWithdrawAddGoods($postValue['addGoodsNo']);

                    }

                    unset($postArray);
                    throw new LayerException(__('상품 승인 요청이 철회 되었습니다.'));

                    break;
            }

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

    }
}
