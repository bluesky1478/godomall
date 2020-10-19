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
namespace Bundle\Widget\Mobile\Proc;

/**
 * HeaderSearchWidget
 *
 * @package Bundle\Controller\Front\Outline
 * @author  Young Eun Jung <atomyang@godo.co.kr>
 *          Jong-tae Ahn <qnibus@godo.co.kr>
 */
class HeaderSearchWidget extends \Widget\Mobile\Widget
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {

        $keywordConfig = gd_policy('search.keyword');
        $hitKeywordConfig = gd_policy('search.hitKeyword');
        $recentKeyword = gd_policy('search.recentKeyword');
        $recentCount = empty($recentKeyword) === false ? $recentKeyword['mobileCount'] : 10;

        if ($keywordConfig['keywordFl'] == 'y' && $keywordConfig['pr_text']) {
            $tmpKey = array_keys($keywordConfig['pr_text']);
            shuffle($tmpKey);

            $url = $keywordConfig['link_url'][$tmpKey[0]];
            $keyword = $keywordConfig['pr_text'][$tmpKey[0]];
        }

        $this->setData('adUrl', gd_isset($url));
        $this->setData('adKeyword', gd_isset($keyword));
        $this->setData('hitKeywordConfig', gd_isset($hitKeywordConfig));

        // 최근 검색어 저장 후 키워드와 날짜로 분리
        $setRecentKeyword = [];
        $recentKeyword = array_slice(json_decode(\Cookie::get('recentKeywordMobile')), 0, $recentCount);
        foreach ($recentKeyword as $key => $val) {
            if (stripos($val, STR_DIVISION) !== false) {
                $setRecentKeyword[$key] = explode(STR_DIVISION, $val);
            } else {
                $setRecentKeyword[$key] = [$val, ''];
            }
            $setRecentKeyword[$key][0] = htmlentities($setRecentKeyword[$key][0]);
        }
        $this->setData('recentCount', gd_isset($recentCount));
        $this->setData('recentKeyword', gd_isset($setRecentKeyword));
    }
}
