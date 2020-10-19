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

use Exception;
use Globals;

class GoodsMustInfoListController extends \Controller\Admin\Controller
{

    /**
     * 필수 정보 관리 페이지
     * [관리자 모드] 필수 정보 관리  페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws Except
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('goods', 'goods', 'mustinfo');

            // --- 모듈 호출
            $mustInfo = \App::load('\\Component\\Goods\\GoodsMustInfo');

            $getData = $mustInfo->getAdminListMustInfo();
            $page = \App::load('Page', '\\Component\\Page\\Page');

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);
            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('sort', $getData['sort']);
            $this->setData('checked', $getData['checked']);
            $this->setData('selected', $getData['selected']);
            $this->setData('page', $page);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/goods_must_info_list.php');

        } catch (Exception $e) {
            throw $e;
        }

    }
}
