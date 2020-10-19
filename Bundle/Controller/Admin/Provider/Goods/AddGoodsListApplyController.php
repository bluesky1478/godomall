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

use Exception;
use Globals;

class AddGoodsListApplyController extends \Controller\Admin\Controller
{
    /**
     * 추가상품 리스트 페이지
     * [관리자 모드] 브랜드 테마 리스트 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws Except
     */
    public function index()
    {

        // --- 메뉴 설정
        $this->callMenu('goods', 'addGoods', 'addGoodsApply');

        // --- 모듈 호출
        $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');

        // --- 추가상품 데이터
        try {
            $getData = $addGoods->getAdminListAddGoods();

            $getData['search']['applyTypeList'] = [
                'all'    => __('전체'),
                'r'   => __('상품등록'),
                'm'  => __('상품수정'),
                'd'  => __('상품삭제'),
            ];

            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        } catch (Exception $e) {
            throw $e;
        }

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('page', $page);


    }
}
