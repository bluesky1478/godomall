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

namespace Bundle\Controller\Admin\Share;

use Exception;
use Request;

/**
 * Class 레이어 회원 그룹 등록 페이지
 * [관리자 모드] 레이어 상품 등록 페이지
 * 설명 : 상품 정보가 필요한 페이지에서 선택할 상품의 리스트
 * @package   Bundle\Controller\Admin\Share
 * @author    yjwee
 * @author    artherot
 */
class LayerMemberGroupController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function index()
    {
        //--- 모듈 호출
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');

        //--- 그룹 데이터
        try {
            /** @var \Bundle\Component\Member\MemberGroup $memberGroup */
            $getData = $memberGroup->getGroupListSearch();

            $getValue = Request::get()->toArray();

            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode', gd_isset($getValue['mode'], 'search'));
            $this->setData('callFunc', gd_isset($getValue['callFunc'], ''));

            $this->setData('data', $getData['data']);

            $this->setData('search', gd_isset($getData['search']));
            $this->getView()->setDefine('layout', 'layout_layer.php');

            $page = \App::load('\\Component\\Page\\Page');
            $this->setData('page', $page);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_member_group.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
