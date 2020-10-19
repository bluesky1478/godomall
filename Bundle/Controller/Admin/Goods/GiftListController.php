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

/**
 * 사은품 리스트
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class GiftListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'gift', 'giftList');

        // --- 모듈 호출
        $gift = \App::load('\\Component\\Gift\\GiftAdmin');

        // --- 사은품 데이터
        try {
            $getData = $gift->getAdminListGift();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // --- 관리자 디자인 템플릿
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
            $this->getView()->setPageName('goods/gift_list.php');

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
