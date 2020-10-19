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
use Component\Goods\GoodsAdmin;
use Component\Storage\Storage;
use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\LayerNotReloadException;
use Globals;
use UserFilePath;

class GoodsImageBatchController extends \Controller\Admin\Controller
{

    /**
     * 상품 이미지 일괄처리 페이지
     * [관리자 모드] 상품 이미지 일괄처리 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @internal param array $get
     * @internal param array $post
     * @internal param array $files
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'batch', 'goodsImageBatch');

        // --- 상품 리스트 데이터
        try {
            $req = \Request::get()->all();
            $req['imageName'] = addslashes($req['imageName']);	// 이미지파일명 검색시 싱글쿼터 오류 수정
            $goods = new GoodsAdmin();
            $data = $goods->getTmpGoodsImage($req);
            $data['pageHtml'] = $data['page']->getPage();
            $goodsCnt = $data['totalCnt'];
            $selected['searchField'][$req['searchField']] = 'selected';
            $req['isApplyGoods'] = empty($req['isApplyGoods']) ? 'all' : $req['isApplyGoods'];
            $checked['isApplyGoods'][$req['isApplyGoods']] = 'checked';
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('req', $req);
        $this->setData('selected', $selected);
        $this->setData('checked', $checked);
        $this->setData('goodsCnt', gd_isset($goodsCnt, 0));
    }
}
