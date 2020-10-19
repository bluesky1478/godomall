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
use Globals;
use Request;

/**
 * [관리자 모드] 레이어 상품 등록 페이지
 * 설명 : 공급사 정보가 필요한 페이지에서 선택할 공급사의 리스트
 *
 * @package Bundle\Controller\Admin\Share
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerScmController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     */
    public function index()
    {
        //--- 상품 데이터
        try {
            //--- 모듈 호출
            $scmAdmin = \App::load('\\Component\\Scm\\ScmAdmin');

            //공급사만 출력
            Request::get()->set('scmKind', 'p');

            $getData = $scmAdmin->getScmAdminList('layer');
            $page = \App::load('\\Component\\Page\\Page');    // 페이지 재설정

            $this->getView()->setDefine('layout', 'layout_layer.php');

            $getValue = Request::get()->toArray();

            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('childRow', $getValue['childRow']);
            $this->setData('mode', gd_isset($getValue['mode'], 'search'));
            $this->setData('disabled', gd_isset($getValue['disabled'], ''));
            $this->setData('callFunc', gd_isset($getValue['callFunc'], ''));
            $this->setData('scmAdmin', $scmAdmin);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('page', $page);
            $this->setData('layerType', gd_isset($getValue['layerType'], ''));
            $this->setData('scmCommissionSet', gd_isset($getValue['scmCommissionSet'], ''));

        } catch (Exception $e) {
            throw $e;
        }
    }
}
