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


use Component\Storage\Storage;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Exception;
use Message;
use Request;

class GoodsPsController extends \Controller\Admin\Goods\GoodsPsController
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
        parent::index();


        // --- 각 배열을 trim 처리
        $postValue = Request::post()->toArray();

        // --- 상품 class
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        try {

            switch ($postValue['mode']) {
                // 상품철회
                case 'applyWithdraw':

                    if (empty($postValue['goodsNo']) === false) {

                        $goods->setApplyWithdrawGoods($postValue['goodsNo']);

                    }

                    unset($postArray);
                    throw new LayerException(__('상품 승인 요청이 철회 되었습니다.'));

                    break;
            }

        } catch (Exception $e) {
            throw $e;
            //throw new LayerException($e->getMessage());
        }
        exit;
    }
}
