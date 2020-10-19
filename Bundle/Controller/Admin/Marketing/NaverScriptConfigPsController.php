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

namespace Bundle\Controller\Admin\Marketing;

use Framework\Debug\Exception\LayerNotReloadException;
use Message;
use Request;

/**
 * 네이버 공통 유입 스크립트 설정 저장 처리 페이지
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class NaverScriptConfigPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        //--- 모듈 호출
        try {
            switch (Request::request()->get('mode')) {
                //--- 네이버 공통 유입 스크립트 설정 저장
                case 'naver_common_inflow_script':
                $policy	= \App::load('\\Component\\Policy\\Policy');
                $policy->saveNaverCommonInflowScript(Request::post()->toArray());
                $this->layer(__('저장이 완료되었습니다.'));
                break;
            }
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }

        exit;
    }
}
