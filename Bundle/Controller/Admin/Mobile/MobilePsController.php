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
use Framework\Debug\Exception\LayerException;
use Exception;
use Message;
use Request;

/**
 *모바일샵 저장 처리 페이지
 *
 * [관리자 모드] 모바일샵 저장 처리 페이지
 *
 * @author    artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class MobilePsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        //--- 각 배열을 trim 처리
        $request = Request::request()->toArray();

        switch ($request['mode']) {
            //--- 기본 정보 저장
            case 'mobile_config':
                // 모듈 호출
                try {
                    // 기본 정보 저장
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveMobileConfig($request);

                    throw new LayerException(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            //--- 디자인 설정 저장
            case 'mobile_design':
                // 모듈 호출
                try {
                    // 기본 정보 저장
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveMobileDesign($request);

                    throw new LayerException(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 모바일샵 메인 상품 진열 및 테마 설정 등록 / 수정
            case 'display_theme_mobile_register':
            case 'display_theme_mobile_modify':
                // 모듈 호출
                try {
                    // 상품 모듈
                    $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
                    $goods->saveInfoDisplayThemeMobile($request);

                    throw new LayerException(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            //--- 모바일샵 각 페이지 설정
            case 'mobile_page_config':
                // 모듈 호출
                try {
                    // 기본 정보 저장
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveMobilePageConfig($request);

                    throw new LayerException(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;
        }
    }
}
