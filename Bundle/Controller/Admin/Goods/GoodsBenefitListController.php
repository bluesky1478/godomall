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
use Request;

class GoodsBenefitListController extends \Controller\Admin\Controller
{

    /**
     * 상품 혜택 관리 리스트
     * @author <cjb3333@godo.co.kr>
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'goods', 'benefit');

        // --- 모듈 호출
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');

        $groupList = $memberGroup->getGroupListSelectBox(['key'=>'sno', 'value'=>'groupNm']);

        // --- 상품 아이콘 데이터
        try {

            $getData = $goodsBenefit->getAdminListGoodsBenefit();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            $goodsBenefitUse = $goodsBenefit->getConfig();

        } catch (Exception $e) {
            throw $e;
        }

        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('groupList', $groupList['data']);
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
        $this->setData('etcIcon', $goods->etcIcon);
        $this->setData('goodsBenefitUse', $goodsBenefitUse);
    }
}
