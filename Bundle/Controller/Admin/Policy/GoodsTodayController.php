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
namespace Bundle\Controller\Admin\Policy;

use Globals;

class GoodsTodayController extends \Controller\Admin\Controller
{

    /**
     * 최근 본 상품 설정 페이지
     * [관리자 모드] 최근 본 상품 설정 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'goods', 'today');

        // --- 최근 본 상품 설정 config 불러오기
        $data = gd_policy('goods.today');

        // --- 기본값 설정
        gd_isset($data['todayHour'], 0);
        gd_isset($data['todayCnt'], 0);

        $this->setData('data', $data);
    }
}
