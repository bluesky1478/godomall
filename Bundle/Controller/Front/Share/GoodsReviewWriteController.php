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
namespace Bundle\Controller\Front\Share;

use Component\Board\Board;
use Component\Board\BoardAdmin;
use Component\Board\BoardWrite;
use Request;
use Framework\Debug\Exception\AlertOnlyException;

class GoodsReviewWriteController extends \Controller\Front\Controller
{

    /**
     * 상품 리뷰 쓰기
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {
        try {

            $req = Request::get()->toArray();

            if (!gd_isset($req['bdId'])) {
                $req['bdId'] = Board::BASIC_GOODS_REIVEW_ID;
            }

            $boardWrite = new BoardWrite($req, true);
            $getData = $boardWrite->getData();

            $bdWrite['cfg'] = gd_isset($boardWrite->cfg);
            $bdWrite['req'] = gd_htmlspecialchars($boardWrite->req);
            $bdWrite['member'] = gd_isset($boardWrite->member);
            $bdWrite['categoryBox'] = $boardWrite->getCategoryBox($getData['category'], 'class="form-control"', true);
            $bdWrite['data'] = gd_htmlspecialchars(gd_isset($getData));
            //if ($boardWrite->req['mode'] == 'reply') $bdWrite['data']['subject'] = ''; @todo: ??
            if(!$getData['goodsData']) $bdWrite['data']['goodsNo'] = Request::get()->get('goodsNo');

            // --- Template_ 호출
            $this->setPageName('goods/goods_review_register');
            $this->setData('bdWrite', gd_isset($bdWrite));
            $this->setData('req', gd_htmlspecialchars($boardWrite->req));
            $this->setData('goodsData', gd_isset($bdWrite['data']['goodsData']));
            $this->setData('pClass', gd_isset($_GET['pClass']));
          //  debug($bdWrite);
            // @formatter:off
            $this->addScript([
                'script/jquery.formprocess.js',
                'script/board.js'
            ]);
            // @formatter:on
        } catch (\Exception $e) {
                throw new AlertOnlyException($e->getMessage());
        }

        unset($getData);
        unset($req);
        unset($db);
        unset($boardWrite);
        unset($goodsData);
    }
}
