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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Worker;

/**
 * Class TargetingGatesDbUrl
 * @package Bundle\Component\Worker
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class TargetingGatesDbUrl extends \Component\Worker\AbstractDbUrl
{
    /**
     * DbUrl 정책 호출
     *
     */
    protected function loadConfig()
    {
        $this->maxCount = 200000;
        $dbUrl = \App::load('Component\\Marketing\\TargetingGates');
        $this->config = $dbUrl->getConfig();
    }

    /**
     * DbUrl 사용함 상태 확인
     *
     * @return mixed
     */
    protected function notUseDbUrl(): bool
    {
        if (!key_exists('tgFl', $this->config)) {
            $this->loadConfig();
        }

        return $this->config['tgFl'] != 'y';
    }

    /**
     * makeDbUrl
     *
     * @param \Generator $goodsGenerator
     * @param int        $pageNumber
     *
     * @return bool
     */
    protected function makeDbUrl(\Generator $goodsGenerator, int $pageNumber): bool
    {
        $request = \App::getInstance('request');
        $this->totalDbUrlPage++;

        $goodsGenerator->rewind();
        while ($goodsGenerator->valid()) {
            if ($this->greaterThanMaxCount()) {
                break;
            }
            $goods = $goodsGenerator->current();
            $goodsGenerator->next();
            if (empty($goods['goodsPriceString']) === false || empty($goods['imageName']) === true) {
                continue;
            }
            $goodsImageSrc = $this->getGoodsImageSrc($goods);

            $cateListCd = [];
            $cateListNm = [];
            if ($goods['cateCd']) {
                if (empty($this->categoryStorage[$goods['cateCd']]) === true) {
                    $cateList = $this->componentCategory->getCategoriesPosition($goods['cateCd'])[0];
                    $this->categoryStorage[$goods['cateCd']] = $cateList;
                }
                $cateList = $this->categoryStorage[$goods['cateCd']];

                if ($cateList) {
                    $cateListCd = array_keys($cateList);
                    $cateListNm = array_values($cateList);
                }
            }

            $this->writeDbUrl('<<<begin>>>');
            $this->writeDbUrl('<<<mapid>>>' . $goods['goodsNo']); // [필수] 상품ID
            $this->writeDbUrl('<<<pname>>>' . gd_htmlspecialchars_stripslashes($goods['goodsNm'])); // [필수] 상품명
            $this->writeDbUrl('<<<price>>>' . gd_money_format($goods['goodsPrice'], false)); // [필수] 상품가격
            $this->writeDbUrl('<<<pgurl>>>' . 'http://' . $this->policy['basic']['info']['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $goods['goodsNo']); // [필수] PC 상품URL
            $this->writeDbUrl('<<<mourl>>>' . 'http://m.' . str_replace('www.', '', $this->policy['basic']['info']['mallDomain']) . '/goods/goods_view.php?goodsNo=' . $goods['goodsNo']); // [필수] MOBILE 상품URL
            if ($request->isCli() && $goods['imageStorage'] == 'local' && (strpos(strtolower($goodsImageSrc), 'http://') === false && strpos(strtolower($goodsImageSrc), 'https://') === false)) {
                $this->writeDbUrl('<<<igurl>>>' . 'http://' . $this->policy['basic']['info']['mallDomain'] . $goodsImageSrc);
            } else {
                $this->writeDbUrl('<<<igurl>>>' . $goodsImageSrc);
            }
            $this->writeDbUrl('<<<cate1>>>' . gd_isset(reset($cateListNm))); // [필수] 업체 카테고리명 (대분류)
            $this->writeDbUrl('<<<caid1>>>' . gd_isset(reset($cateListCd))); // [필수] 업체 카테고리코드 (대분류)
            $this->writeDbUrl('<<<ftend>>>');
            $this->totalDbUrlData++;
        }

        return true;
    }

    protected function writeDbUrl($contents)
    {
        parent::writeDbUrl(@iconv('UTF-8', 'EUC-KR//IGNORE', $contents));
    }
}
