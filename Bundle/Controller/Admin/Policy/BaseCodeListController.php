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
 * @link      http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Policy;

use Request;

class BaseCodeListController extends \Controller\Admin\Controller
{

    /**
     * 코드관리 리스트 페이지
     * [관리자 모드] 코드관리 리스트 페이지
     *
     * @author    gise, artherot
     * @version   1.0
     * @since     1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     *
     * @param array $get
     * @param array $post
     * @param array $files
     *
     * @throws Except
     */
    public function index()
    {

        $request = \App::getInstance('request');

        // --- 메뉴 설정
        $this->callMenu('policy', 'basic', 'code');

        $getValue = $request->get()->toArray();
        unset($getValue['popupMode']);
        $mallSno = $request->get()->get('mallSno', 1);
        // --- 모듈 호출
        $code = \App::load('\\Component\\Code\\Code', $mallSno);

        // --- 코드 정보
        try {
            $categoryGroupCd = $code->codeFetch('categoryGroup');
            $requestCategoryGroupCd = $request->get()->get('categoryGroupCd');
            if (empty($requestCategoryGroupCd)) {
                $requestCategoryGroupCd = '01';
            }
            $_gcode = $code->codeFetch('getGroupCode', $requestCategoryGroupCd);
            foreach ($_gcode as $val) {
                if ($val['itemNm'] === '환불수단') continue;
                $gcode[$val['itemCd']] = $val['itemNm'];
            }

            $getData = $code->codeFetch('getCodeList', $getValue);

        } catch (\Exception $e) {
            $this->layer($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        if ($request->get()->get('popupMode', 'n') == 'y') {
            $this->getView()->setDefine('layout', 'layout_blank.php');
            $this->setData('popupMode', 'y');
        }

        $this->setData('mallSno', $mallSno);
        $this->setData('getValue', $getValue);
        $this->setData('categoryGroupCd', $categoryGroupCd);
        $this->setData('gcode', $gcode);
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('count', $getData['count']);

        $gGlobal = $this->getData('gGlobal');
        foreach ($gGlobal['useMallList'] as &$item) {
            $item['tabUrl'] = '..' . $request->getPhpSelf() . '?mallSno=' . $item['sno'] . '&categoryGroupCd=' . $getData['search']['categoryGroupCd'] . '&groupCd=' . $getData['search']['groupCd'];
            if ($request->get()->get('popupMode', 'n') == 'y') {
                $item['tabUrl'] .= '&popupMode=y';
            }
        }
        $this->setData('gGlobal', $gGlobal);

        $this->addCss(
            [
                '../script/jquery/colorpicker-master/jquery.colorpicker.css',
            ]
        );
        $this->addScript(
            [
                'jquery/colorpicker-master/jquery.colorpicker.js',
            ]
        );
    }
}
