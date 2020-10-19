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

class GoodsMustInfoRegisterController extends \Controller\Admin\Controller
{

    /**
     * 필수정보 관리
     * [관리자 모드] 필수 정보 등록 / 수정 페이지
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
        // --- 사은품 데이터
        try {
            // --- 메뉴 설정
            if (Request::get()->get('sno') > 0) {
                $this->callMenu('goods', 'goods', 'mustinfo_modify');
            } else {
                $this->callMenu('goods', 'goods', 'mustinfo_register');
            }

            // --- 모듈 설정
            $mustInfo = \App::load('\\Component\\Goods\\GoodsMustInfo');

            $data = $mustInfo->getDataMustInfo(Request::get()->get('sno'));


            if ($data['info']) {
                $data['addMustInfo'] = json_decode($data['info']);
            }
            $scmAdmin = \App::load('\\Component\\Scm\\ScmAdmin');
            $tmpData = $scmAdmin->getScmInfo($data['data']['scmNo'], 'companyNm');
            $data['data']['scmNoNm'] = $tmpData['companyNm'];

            // --- 관리자 디자인 템플릿
            if (Request::get()->get('popupMode')) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }

            $this->setData('conf', $conf);
            $this->setData('data', gd_htmlspecialchars($data['data']));
            $this->setData('checked', $data['checked']);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/goods_must_info_register.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
