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

use Exception;
use Globals;
use Request;

class GoodsOptionRegisterController extends \Controller\Admin\Controller
{

    /**
     * 자주쓰는 상품 옵션 관리 등록 / 수정 페이지
     * [관리자 모드] 자주쓰는 상품 옵션 관리 등록 / 수정 페이지
     *
     * @author artherot
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
        // --- 자주쓰는 상품 옵션 데이터
        try {
            $getValue = Request::get()->toArray();

            // --- 메뉴 설정
            if ($getValue['sno'] > 0) {
                $this->callMenu('goods', 'goods', 'option_modify');
            } else {
                $this->callMenu('goods', 'goods', 'option_reg');
            }

            // --- 모듈 설정
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');


            $data = $goods->getDataManageOption(gd_isset($getValue['sno']));

            $scmAdmin = \App::load('\\Component\\Scm\\ScmAdmin');
            $tmpData = $scmAdmin->getScmInfo($data['data']['scmNo'], 'companyNm');
            $data['data']['scmNoNm'] = $tmpData['companyNm'];

            // --- 관리자 디자인 템플릿
            if (isset($getValue['popupMode']) === true) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }
            $this->setData('data', gd_htmlspecialchars($data['data']));
            $this->setData('checked', $data['checked']);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/goods_option_register.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
