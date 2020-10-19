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
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\HttpException;
use Framework\Debug\Exception\AlertBackException;
use Message;
use Globals;
use Request;

class CategoryPsController extends \Controller\Admin\Controller
{

    /**
     * 카테고리 관련 처리 페이지
     * [관리자 모드] 카테고리 관련 수정 페이지
     *
     * @author    artherot
     * @version   1.0
     * @since     1.0
     *
     * @param array $get
     * @param array $post
     * @param array $files
     *
     * @throws Except
     * @throws LayerException
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        // --- 모듈 호출
        $getValue = Request::get()->toArray();
        $postValue = Request::post()->toArray();


        try {
            // --- 카테고리 타입에 따른 설정 (상품,브랜드)
            gd_isset($getValue['cateType'], 'goods');
            if ($getValue['cateType'] == 'goods') {
                $objName = 'CategoryAdmin';
            } else if ($getValue['cateType'] == 'brand') {
                $objName = 'BrandCategoryAdmin';
            } else {
                throw new \Exception(__('잘못된 카테고리 타입입니다.'));
            }
        } catch (\Exception $e) {
            if (!Request::isAjax()) {
                throw new AlertBackException($e->getMessage());
            }
        }

        try {


            // --- 카테고리 class
            // @todo 브랜드 카테고리 클래스 분리 필요
            $cate = \App::load('\\Component\\Category\\CategoryAdmin', $getValue['cateType']);

            switch (Request::request()->get('mode')) {
                // 카테고리 생성
                case 'register':


                        $cate->saveInfoCategory($postValue);


                        $this->layer(__('저장이 완료되었습니다.'));

                        throw new LayerException();

                    break;

                // 하위 카테고리 생성
                case 'subcreate':
                    echo $cate->saveInfoCategorySub($postValue);
                    exit();
                    break;

                // 카테고리 수정
                case 'modify':


                        $cate->saveInfoCategoryModify($postValue);
                        if ($out = ob_get_clean()) {
                            throw new Except('ECT_SAVE_FAIL', $out);
                        }
                        $refreshStr = 'parent.$.tree.reference(\'categoryTree\').refresh();';

                        $this->layer(__('저장이 완료되었습니다.'), null, null, $refreshStr);

                    break;

                // 카테고리 이름 변경
                case 'rename':
                    $cate->setRenameCategory($postValue);
                    exit();
                    break;

                // 카테고리 이동
                case 'move':
                    echo $cate->setMoveCategory($postValue['cateCd'], $postValue['targetCateCd'], $postValue['moveLoc']);
                    exit();
                    break;

                // 카테고리 삭제
                case 'delete':
                    echo $cate->setDeleteCategory($postValue['cateCd']);
                    exit();
                    break;

                // 카테고리 테마
                case 'goods_theme_register':
                case 'goods_theme_modify':
                    // @todo : skinAdmin 를 더이상 사용못함 다르게 구성할것
                    /*
                     * try { ob_start(); // 카테고리테마저장 $dataSno = $cate->saveInfoCategoryTheme($postValue); // 스킨테마생성 try { $skinAdmin = \App::load('\\Component\\Skin\\SkinAdmin'); $skinAdmin->setSkin(Globals::get('gSkin.frontSkinWork')); $skinAdmin->createCategoryThemeContainer($postValue['themeId'], $postValue['themeNm'], $postValue['listType'], $postValue['recomType'], $postValue['subcateType']); } catch (Except $e) { // 카테고리테마 등록시 스킨테마생성 실패시 Clear if ($_REQUEST['mode'] == 'goods_theme_register') { $skinAdmin->deleteCategoryThemeContainer($postValue['themeId']); $cate->setDeleteCategoryTheme($dataSno); } throw new Except($e->ectName, $e->ectMessage); } if ($out = ob_get_clean()) { throw new Except('ECT_SAVE_FAIL', $out); } $param = ''; if (empty($postValue['popupMode']) === false) { $param = 'popupMode=' . $postValue['popupMode'] . '&amp;'; } throw new LayerException(null, null, null, null, 1000, 'parent.location.replace("../goods/category_theme_register.php?' . $param . 'sno=' . $dataSno . '");'); } catch (Except $e) { $e->actLog(); $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : ''); throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0); }
                     */
                    break;

                // 카테고리 테마 삭제
                case 'goods_theme_delete':
                    // @todo : skinAdmin 를 더이상 사용못함 다르게 구성할것
                    /*
                     * try { ob_start(); list($tData) = $cate->getInfoCategoryTheme($postValue['dataSno']); // 스킨테마삭제 $skinAdmin = \App::load('\\Component\\Skin\\SkinAdmin'); $skinAdmin->setSkin(Globals::get('gSkin.frontSkinWork')); $skinAdmin->deleteCategoryThemeContainer($tData['themeId']); // 카테고리테마삭제 $cate->setDeleteCategoryTheme($postValue['dataSno']); } catch (Except $e) { $e->actLog(); // echo ($e->ectMessage); exit(); } exit();
                     */
                    break;

                // 브랜드 테마
                case 'brand_theme_register':
                case 'brand_theme_modify':
                    // @todo : skinAdmin 를 더이상 사용못함 다르게 구성할것
                    /*
                     * try { ob_start(); // 브랜드테마저장 $dataSno = $cate->saveInfoCategoryTheme($postValue); // 스킨테마생성 try { $skinAdmin = \App::load('\\Component\\Skin\\SkinAdmin'); $skinAdmin->setSkin(Globals::get('gSkin.frontSkinWork')); $skinAdmin->createBrandThemeContainer($postValue['themeId'], $postValue['themeNm'], $postValue['listType'], $postValue['recomType'], $postValue['subcateType']); } catch (Except $e) { // 브랜드테마 등록시 스킨테마생성 실패시 Clear if ($_REQUEST['mode'] == 'brand_theme_register') { $skinAdmin->deleteBrandThemeContainer($postValue['themeId']); $cate->setDeleteCategoryTheme($dataSno); } throw new Except($e->ectName, $e->ectMessage); } if ($out = ob_get_clean()) { throw new Except('ECT_SAVE_FAIL', $out); } $param = ''; if (empty($postValue['popupMode']) === false) { $param = 'popupMode=' . $postValue['popupMode'] . '&amp;'; } throw new LayerException(null, null, null, null, 1000, 'parent.location.replace("../goods/brand_theme_register.php?' . $param . 'sno=' . $dataSno . '");'); } catch (Except $e) { $e->actLog(); $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : ''); throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0); }
                     */
                    break;

                // 브랜드 테마 삭제
                case 'brand_theme_delete':
                    // @todo : skinAdmin 를 더이상 사용못함 다르게 구성할것
                    /*
                     * try { ob_start(); list($tData) = $cate->getInfoCategoryTheme($postValue['dataSno']); // 스킨테마삭제 $skinAdmin = \App::load('\\Component\\Skin\\SkinAdmin'); $skinAdmin->setSkin(Globals::get('gSkin.frontSkinWork')); $skinAdmin->deleteBrandThemeContainer($tData['themeId']); // 브랜드테마삭제 $cate->setDeleteCategoryTheme($postValue['dataSno']); } catch (Except $e) { $e->actLog(); // echo ($e->ectMessage); exit(); } exit();
                     */
                    break;

                // 테마코드중복확인
                case 'overlapThemeId':
                    try {
                        ob_start();
                        $result = $cate->overlapThemeId($postValue['themeId']);
                        if ($out = ob_get_clean()) {
                            throw new Except('ECT_SAVE_FAIL', $out);
                        }
                        echo json_encode($result);
                    } catch (Except $e) {
                        $e->actLog();
                        throw new HttpException($e->ectMessage, 500);
                    }
                    break;

                // 상품 매핑
                case 'goods_mapping':
                    // -- 상품 모듈
                    try {
                        ob_start();
                        $cate->setGoodsMapping();
                        if ($out = ob_get_clean()) {
                            throw new Except('ECT_SAVE_FAIL', $out);
                        }
                        throw new LayerException(__('상품 매핑이 완료 되었습니다.'));
                    } catch (Except $e) {
                        $e->actLog();
                        $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : '');
                        throw new LayerException(__('상품 매핑이 실패하였습니다.') . $item, null, null, null, 0);
                    }
                    break;

                // 상품 매핑
                case 'goods_mapping_layer':
                    // -- 상품 모듈
                    try {
                        ob_start();
                        $cate->setGoodsMapping();
                        if ($out = ob_get_clean()) {
                            throw new Except('ECT_SAVE_FAIL', $out);
                        }
                    } catch (Except $e) {
                        $e->actLog();
                        echo __('상품 매핑이 실패하였습니다.');
                    }
                    break;
            }


        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }

    }
}
