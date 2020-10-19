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
namespace Bundle\Controller\Admin\Scm;

use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

class ScmAdjustTotalController extends \Controller\Admin\Controller
{
    /**
     * 공급사 통합 정산 관리
     * [관리자 모드] 공급사 통합 정산 관리
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
        $this->callMenu('scm', 'adjust', 'scmAdjustTotal');

        // 모듈호출
        $scmAdjust = \App::load('\\Component\\Scm\\ScmAdjust');

        // --- 상품 리스트 데이터
        try {
            $getData = $scmAdjust->getScmAdjustTotal(Request::get()->all());
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('checked', $getData['checked']);
    }
}
