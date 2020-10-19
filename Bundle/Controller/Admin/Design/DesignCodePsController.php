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

namespace Bundle\Controller\Admin\Design;

use Message;
use Request;

/**
 * 디자인 치환코드 검색처리
 * @author Bagyj <kookoo135@godo.co.kr>
 */
class DesignCodePsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // POST 파라메터
        $postValue = Request::post()->toArray();

        // 페이지 ID
        $getPageID = $postValue['designPageId'];

        $dCode = \App::load('\\Component\\Design\\DesignCode');

        $fileName = str_replace('.html', '', $getPageID);
        $commonFuncCode = $commonVarCode = $designCode = '';

        // 공통으로 사용되는 치환코드 load
        if (strpos($getPageID, '.html') !== false) {
            $jsonData = '';
            $getDesignCode = $dCode->getDesignCode($fileName);
            $jsonData .= '
                    <table width="100%" class="design-code-tbl">
                        <tr>
                            <th>' . $getPageID . ' ' . __('치환코드') . '</th>
                        </tr>
                        ' . @implode($getDesignCode, '') . '
                    </table>
                ';

            //공통변수
            $getCommonVarCode = $dCode->getDesignCode('common_variable');
            $jsonData .= '
                    <table width="100%" class="design-code-tbl">
                        <tr>
                            <th>' . __('공통변수 치환코드') . '</th>
                        </tr>
                        ' . @implode($getCommonVarCode, '') . '
                    </table>
                ';

            // 공통함수
            $getCommonFuncCode = $dCode->getDesignCode('common_function');
            $jsonData .= '
                    <table width="100%" class="design-code-tbl">
                        <tr>
                            <th>' . __('공통함수 치환코드') . '</th>
                        </tr>
                        ' . @implode($getCommonFuncCode, '') . '
                    </table>
                ';
            $this->json($jsonData);
        }
    }
}
