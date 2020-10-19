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
use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertOnlyException;
use Message;

class GoodsQnaWriteController extends \Controller\Front\Controller
{

    /**
     * 상품 QNA 쓰기
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
                $req['bdId'] = Board::BASIC_GOODS_QA_ID;
            }

            $boardWrite = new BoardWrite($req, true);
            $getData = $boardWrite->getData();

            $bdWrite['cfg'] = gd_isset($boardWrite->cfg);
            $bdWrite['req'] = gd_htmlspecialchars($boardWrite->req);
            $bdWrite['member'] = gd_isset($boardWrite->member);
            $bdWrite['categoryBox'] = $boardWrite->getCategoryBox($getData['category'], 'class="form-control"', true);
            $bdWrite['data'] = gd_htmlspecialchars(gd_isset($getData));
            if ($boardWrite->req['mode'] == 'reply') $bdWrite['data']['subject'] = '';
            //if(isset($getData['goodsData'])) $bdWrite['data']['goodsNo'] = $bdWrite['data']['goodsData'][0]['goodsNo'];
            if(!$getData['goodsData']) $bdWrite['data']['goodsNo'] = Request::get()->get('goodsNo');
            // --- Template_ 호출
            $this->setPageName('goods/goods_qna_register');
            $this->setData('bdWrite', gd_isset($bdWrite));
            $this->setData('req', gd_htmlspecialchars($boardWrite->req));
            $this->setData('goodsData', gd_isset($bdWrite['data']['goodsData'][0]));
            $this->setData('pClass', gd_isset($_GET['pClass']));

            $this->addScript([
                'script/jquery.formprocess.js',
                'script/board.js'
            ]);

        } catch (\Exception $e) {
            if (preg_match('/ECT_/', $e->ectName) == 1) {
                $item = ($e->getMessage() ? ' - ' . str_replace('\n', ' - ', $e->ectMessage) : '');
                throw new AlertCloseException(__('안내') . $item);

            } else {
                $e->actLog();
                // echo ($e->ectMessage);
                throw new AlertOnlyException(__('오류') . ' - ' . __('처리중에 오류가 발생하여 실패 하였습니다.'));
            }
        }

        unset($getData);
        unset($req);
        unset($db);
        unset($boardWrite);
        unset($goodsData);
    }
}
