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
namespace Bundle\Controller\Admin\Provider\Scm;

use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Session;

class ScmAdjustListController extends \Controller\Admin\Controller
{
    /**
     * 공급사 정산 리스트
     * [관리자 모드] 공급사 정산 리스트
     *
     * @author su
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
        $this->callMenu('scm', 'adjust', 'scmAdjust');

        // 모듈호출
        $scmAdjust = \App::load('\\Component\\Scm\\ScmAdjust');

        // --- 상품 리스트 데이터
        try {
            // 공급사 정보 추가
            $getValue['scmNo'] = Session::get('manager.scmNo');
            $getData = $scmAdjust->getScmAdjustList(Session::get('manager.scmNo'));
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            $convertGetData = $scmAdjust->convertScmAdjustArrData($getData['data']);
            $selectBoxScmAdjustState = $scmAdjust->scmAdjustState;
            unset($selectBoxScmAdjustState[1]);
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData['data']);
        $this->setData('conventData', $convertGetData);
        $this->setData('selectBoxScmAdjustState', $selectBoxScmAdjustState);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
        $this->setData('scmNo', $getValue['scmNo']);
    }
}
