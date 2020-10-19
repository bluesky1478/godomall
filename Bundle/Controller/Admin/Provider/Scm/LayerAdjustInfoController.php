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

use Component\Scm\ScmAdjust;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

class LayerAdjustInfoController extends \Controller\Admin\Controller
{

    /**
     * 레이어 정산 상세정보 페이지
     * [관리자 모드] 레이어 정산 상세정보 페이지
     * 설명 : 정산 리스트에서 정산 상세정보 페이지
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
        // --- 모듈 호출
        $scmAdjust = new ScmAdjust();

        // --- 상품 데이터
        try {
            $getData = $scmAdjust->getScmAdjustDetailList(Request::post()->get('scmAdjustNo'));
            $convertGetData = $scmAdjust->convertScmAdjustArrData([$getData['scmAdjustData']]);
            $getLogData = $scmAdjust->getScmAdjustLogInfo(Request::post()->get('scmAdjustNo'), '*', null, true);
            $convertGetLogData = $scmAdjust->convertScmAdjustLogArrData($getLogData);

            // --- 관리자 디자인 템플릿
            if ($getData['scmAdjustData']['scmAdjustType'] == 'o' || $getData['scmAdjustData']['scmAdjustType'] == 'oa') {
                $pageName = 'layer_adjust_order_info.php';
            } else {
                $pageName = 'layer_adjust_delivery_info.php';
            }
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . $pageName);
            $this->setData('scmAdjustData', $getData['scmAdjustData']);
            $this->setData('data', $getData['data']);
            $this->setData('convertGetData', $convertGetData);
            $this->setData('dataLog', $getLogData);
            $this->setData('convertGetLogData', $convertGetLogData);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
    }
}
