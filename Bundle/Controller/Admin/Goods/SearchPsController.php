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

use Framework\Debug\Exception\LayerException;
use Request;
use Framework\Utility\ArrayUtils;

class SearchPsController extends \Controller\Admin\Controller
{
    /**
     * 통합 검색 관련 처리 페이지
     *
     * @version 1.0
     * @since 1.0
     * @throws Except
     * @throws LayerException
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        $postValue = Request::post()->toArray();

        try {
            switch($postValue['mode']) {
                case 'search_settings':
                    if(is_array($postValue['keyword'])) {
                        //통합검색 조건 선택 설정
                        gd_set_policy('search.terms', $postValue['terms']);
                    }

                    if(is_array($postValue['keyword'])) {
                        //검색키워드 설정
                        $postValue['keyword']['pr_text'] = ArrayUtils::removeEmpty($postValue['keyword']['pr_text']);
                        $postValue['keyword']['link_url'] = ArrayUtils::removeEmpty($postValue['keyword']['link_url']);
                        gd_set_policy('search.keyword', $postValue['keyword']);
                    }

                    if(is_array($postValue['recent'])) {
                        //최근 검색어 설정
                        gd_set_policy('search.recentKeyword', $postValue['recent']);
                    }

                    //인기 검색어 설정
                    $postValue['hitKeyword']['keyword'] = ArrayUtils::removeEmpty($postValue['hitKeyword']['keyword']);
                    gd_set_policy('search.hitKeyword', $postValue['hitKeyword']);

                    if(is_array($postValue['quick'])) {
                        //빠른 검색 설정
                        if(!gd_isset($postValue['quick']['mobileFl'])) $postValue['quick']['mobileFl'] = "n";
                        gd_set_policy('search.quick', $postValue['quick']);
                    }

                    $this->layer(__('저장이 완료되었습니다.'));
                    break;
            }

        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }
}
