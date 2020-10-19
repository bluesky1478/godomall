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

use Component\Goods\Populate;
use Exception;
use Globals;
use App;
use Request;
/**
 * 인기상품
 * @author <kookoo135@godo.co.kr>
 */
class PopulateListController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'displayConfig', 'populate');

        // 모듈호출
        $populate = \App::load('\\Component\\Goods\\Populate');

        // --- 상품 리스트 데이터
        try {

            $getData = $populate->getPopulateList();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // --- 관리자 디자인 템플릿

            $this->getView()->setDefine('goodsSearchFrm',  Request::getDirectoryUri() . '/populate_list_search.php');

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);

            $this->setData('data', $getData['listData']);
            $this->setData('search', $getData['listSearch']);
            $this->setData('selected', $getData['listSelected']);
            $this->setData('sort', $getData['sort']);
            $this->setData('page', $page);
            $this->setData('totalCnt', $populate->getTotalPopulateThemeCnt());

        } catch (Exception $e) {
            throw $e;
        }

    }
}
