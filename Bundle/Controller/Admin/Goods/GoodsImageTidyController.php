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

use App;
use Framework\Debug\Exception\Except;
use Globals;

class GoodsImageTidyController extends \Controller\Admin\Controller
{

    /**
     * 상품 이미지 일괄정리 페이지
     * [관리자 모드] 상품 이미지 일괄정리 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     */
    public function index()
    {

        // --- 메뉴 설정
        $this->callMenu('goods', 'batch', 'imageTidy');

        // --- 상품 리스트 데이터
        try {
            ob_start();

            $strSQL = 'SELECT count(goodsNo) as cnt FROM ' . DB_GOODS . ' g WHERE g.imageStorage = \'local\'';
            list($goodsCnt) = App::getInstance('DB')->fetch($strSQL, 'row');

            if ($out = ob_get_clean()) {
                throw new Except('ECT_LOAD_FAIL', $out);
            }
        } catch (Except $e) {
            $e->actLog();
            // echo ($e->ectMessage);
        }

        // --- 관리자 디자인 템플릿
        $this->setData('goodsCnt', gd_isset($goodsCnt, 0));
    }
}
