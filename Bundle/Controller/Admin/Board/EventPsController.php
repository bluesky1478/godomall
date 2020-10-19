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
namespace Bundle\Controller\Admin\Board;

use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\Framework\Debug\Exception;
use Framework\Utility\ArrayUtils;
use Message;
use Request;

class EventPsController extends \Controller\Admin\Controller
{

    /**
     * Description
     * @throws Except
     */
    public function index()
    {

        /**
         * 게시판 처리
         *
         * @author sunny
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 모듈 호출
        switch (Request::post()->get('mode')) {
            case 'regist':
                try {
                    ob_start();
                    $eventAdmin = \App::load('\\Component\\Event\\EventAdmin');
                    $eventAdmin->insertData(Request::post()->toArray());
                    if ($out = ob_get_clean()) {
                        throw new Except(__('처리중에 오류가 발생하여 실패되었습니다.'), $out);
                    }
                    throw new LayerException();
                } catch (Except $e) {
                    $e->actLog();
                    $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                }
                break;
            case 'modify':
                try {
                    ob_start();
                    $eventAdmin = \App::load('\\Component\\Event\\EventAdmin');
                    $eventAdmin->modifyData(Request::post()->toArray());
                    if ($out = ob_get_clean()) {
                        throw new Except(__('처리중에 오류가 발생하여 실패되었습니다.'), $out);
                    }
                    throw new LayerException();
                } catch (Except $e) {
                    $e->actLog();
                    $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                }
                break;
            case 'delete':
                try {
                    ob_start();
                    $eventAdmin = \App::load('\\Component\\Event\\EventAdmin');
                    if (!Request::post()->get('sno')) {
                        throw new Except(__('처리중에 오류가 발생하여 실패되었습니다.'), $out);
                    }
                    $eventAdmin->deleteData(Request::post()->get('sno'));
                    if ($out = ob_get_clean()) {
                        throw new Except(__('처리중에 오류가 발생하여 실패되었습니다.'), $out);
                    }
                    throw new LayerException(__('삭제 되었습니다.'));
                } catch (Except $e) {
                    $e->actLog();
                    $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                }
                break;
            case 'getGoodsCate':
                echo '{';
                try {
                    if (ArrayUtils::isEmpty(Request::post()->get('goodsNo')) === false) {
                        $eventAdmin = \App::load('\\Component\\Event\\EventAdmin');
                        $cateData = gd_str2js(gd_htmlspecialchars($eventAdmin->getCategory(implode(INT_DIVISION, Request::post()->get('goodsNo')), 'category')));
                        echo '[{';
                        $arrCate = array();
                        if (ArrayUtils::isEmpty($cateData) === false) {
                            foreach ($cateData as $key => $val) {
                                $arrCate[] = '"' . $key . '":"' . $val . '"';
                            }
                            echo implode(',', $arrCate);
                        }
                        echo '},';
                        unset($arrCate);

                        $brandData = gd_str2js(gd_htmlspecialchars($eventAdmin->getCategory(implode(INT_DIVISION, Request::post()->get('goodsNo')), 'brand')));
                        echo '{';
                        $arrBrand = array();
                        if (ArrayUtils::isEmpty($brandData) === false) {
                            foreach ($brandData as $key => $val) {
                                $arrBrand[] = '"' . $key . '":"' . $val . '"';
                            }
                            echo implode(',', $arrBrand);
                        }
                        echo '}]';
                        unset($arrBrand);
                    }
                } catch (Except $e) {
                    $e->actLog();
                }
                echo '}';
                break;
        }
    }
}
