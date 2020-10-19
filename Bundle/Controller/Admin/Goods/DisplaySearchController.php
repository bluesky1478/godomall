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

use Globals;
use Request;

/**
 * 검색 페이지 상품진열
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class DisplaySearchController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'display', 'search');

        try {
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
            $data = $goods->getDateSearchDisplay();

            $displayConfig = \App::load('\\Component\\Display\\DisplayConfigAdmin');
            $data['data']['set']['pcThemeList'] = $displayConfig->getInfoThemeConfigCate('A', 'n');
            $data['data']['set']['mobileThemeList'] = $displayConfig->getInfoThemeConfigCate('A','y');
            $data['data']['set']['sortList'] = $displayConfig->goodsSortList;
        } catch (\Exception $e) {
            throw $e;
        }

        $this->setData('msgFl', gd_installed_date('2017-03-09'));
        $this->setData('data',$data['data']);
        $this->setData('checked', $data['checked']);
        $this->setData('selected', $data['selected']);
    }
}
