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
namespace Bundle\Controller\Admin\Goods;

use Framework\Debug\Exception\Except;
use Exception;
use Globals;
use Request;

class DisplayConfigNaviController extends \Controller\Admin\Controller
{

    /**
     * 품절상품 진열 페이지
     * [관리자 모드] 메인 진열 리스트 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws Except
     */
    public function index()
    {

        // --- 메뉴 설정
        $this->callMenu('goods', 'category', 'navi');


        // --- 상품 아이콘 데이터
        try {

            $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');

            $data = $display->getDateNaviDisplay();

        } catch (Exception $e) {
            throw $e;
        }

        $this->setData('data',$data['data']);
        $this->setData('checked', $data['checked']);

    }
}
