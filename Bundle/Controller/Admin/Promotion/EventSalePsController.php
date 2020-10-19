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

namespace Bundle\Controller\Admin\Promotion;

use Framework\Debug\Exception\LayerException;
use Request;
use Message;

class EventSalePsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();

        // --- 상품노출 class
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        try {

            switch ($postValue['mode']) {
                // 테마 등록 / 수정
                // 메인 상품 진열 및 테마 설정 등록 / 수정
                case 'main_register':
                case 'main_modify':
                    $goods->saveInfoDisplayTheme($postValue);
                    throw new LayerException(__('저장이 완료되었습니다.'));

                    break;

                // 메인 상품 진열 및 테마 설정 삭제
                case 'main_delete':

                    if (empty($postValue['sno']) === false) {
                        foreach ($postValue['sno'] as $sno) {
                            $goods->setDeleteDisplayTheme($sno,$postValue['themeCd'][$sno]);
                        }
                    }

                    unset($postArray);
                    throw new LayerException(__('삭제 되었습니다.'));

                    break;

                // 메인 상품 진열 및 테마 설정 삭제
                case 'search_register':

                    //검색어 기본 진열
                    gd_set_policy('search.goods', $postValue['goods']);

                    //검색키워드 설정
                    $postValue['keyword']['pr_text'] = ArrayUtils::removeEmpty($postValue['keyword']['pr_text']);
                    $postValue['keyword']['link_url'] = ArrayUtils::removeEmpty($postValue['keyword']['link_url']);
                    gd_set_policy('search.keyword', $postValue['keyword']);

                    //인기 검색어 설정
                    $postValue['hitKeyword']['keyword'] = ArrayUtils::removeEmpty($postValue['hitKeyword']['keyword']);
                    gd_set_policy('search.hitKeyword', $postValue['hitKeyword']);

                    //빠른 검색 설정
                    if(!gd_isset($postValue['quick']['mobileFl'])) $postValue['quick']['mobileFl'] = "n";
                    gd_set_policy('search.quick', $postValue['quick']);

                    throw new LayerException(__('저장이 완료되었습니다.'));

                    break;
                case 'soldout_register':

                    $goods->saveInfoDisplaySoldOut($postValue);

                    throw new LayerException(__('저장이 완료되었습니다.'));

                    break;
                case 'search_theme':

                    $data = $goods->getJsonListDisplayTheme($postValue['mobileFl']);

                    echo $data;
                    exit;

                    break;

            }

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }
}
