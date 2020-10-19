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

namespace Bundle\Controller\Admin\Mobile;

use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ImageUtils;
use Globals;
use Request;
use Message;

/**
 * 모바일샵 메인 상품 진열 및 테마 설정 페이지
 *
 * [관리자 모드] 모바일샵 메인 상품 진열 및 테마 설정 페이지
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class MobileGoodsDisplayController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        //--- 모듈 호출
        echo strtok('', '?');

        //--- 메뉴 설정
        $this->callMenu('mobile', 'goods', 'display');

        //--- 모듈 설정
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        //--- 모바일샵 메인 상품 진열 및 테마 설정 불러오기
        $data = $goods->getDataDisplayThemeMobile(Request::get()->get('sno', 1));

        //--- 모바일샵 메인 상품 진열 및 테마 설정 전체
        $theme = $goods->getDisplayThemeMobileInfo(null, true);

        //--- 이미지 설정 및 필요한 이미지만 추출
        $confImage = gd_policy('goods.image');
        ImageUtils::sortImageConf($confImage, array('list', 'detail', 'magnify'));



        //--- 관리자 디자인 템플릿
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);
        $this->setData('data', gd_htmlspecialchars($data['data']));
        $this->setData('theme', gd_htmlspecialchars($theme));
        $this->setData('confImage', $confImage);
        $this->setData('checked', $checked = $data['checked']);


    }
}
