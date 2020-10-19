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
use Globals;

class GoodsImghostController extends \Controller\Admin\Controller
{
    /**
     * 이미지호스팅일괄전환
     *
     * @author sunny
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

        // --- 메뉴 설정
        $this->callMenu('goods', 'batch', 'imghost');

        // --- 모듈 호출
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $brand = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $imgHost = \App::load('\\Component\\File\\ImgHost', '');

        // --- 상품 리스트 데이터
        try {
            ob_start();

            $getData = $goods->getAdminListGoods();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            $getIcon = $goods->getManageGoodsIconInfo();

            if ($out = ob_get_clean()) {
                throw new Except('ECT_LOAD_FAIL', $out);
            }
        } catch (Except $e) {
            // $e->actLog();
            echo ($e->ectMessage);
        }

        // --- 관리자 디자인 템플릿
        $this->setData(
            'headerScript', [
            PATH_ADMIN_GD_SHARE . 'script/jquery/jquery.multi_select_box.js',
            PATH_ADMIN_GD_SHARE . 'script/ImgHost.js',
        ]
        );

        $this->setData('goods', $goods);
        $this->setData('cate', $cate);
        $this->setData('brand', $brand);
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('page', $page);
        $this->setData('getIcon', $getIcon);
        $this->setData('imgHost', $imgHost);
    }
}
