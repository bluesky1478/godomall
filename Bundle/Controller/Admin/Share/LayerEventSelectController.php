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

/**
 * 기획전 선택 레이어 페이지
 * [관리자 모드] 기획전 관련 설정
 *
 * @package Bundle\Controller\Admin\Order
 * @author  <bumyul2000@godo.co.kr>
 */
class LayerEventSelectController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // 리퀘스트
            $getValue = \Request::get()->toArray();

            // --- 모듈 호출
            $event = \App::load('\\Component\\Goods\\GoodsAdmin');
            $getData = $event->getAdminListDisplayTheme('event', 'layer');

            // 페이지
            $page = \App::load('\\Component\\Page\\Page');
            $this->setData('page', $page);

            // 템플릿 변수
            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('layerTargetTable', $getValue['layerTargetTable']);
            $this->setData('layerTargetCheckboxName', $getValue['layerTargetCheckboxName']);
            $this->setData('layerTargetHiddenValueName', $getValue['layerTargetHiddenValueName']);
            $this->setData('mode', gd_isset($getValue['mode']));
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('checked', gd_isset($getData['checked']));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('page', $page);

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
