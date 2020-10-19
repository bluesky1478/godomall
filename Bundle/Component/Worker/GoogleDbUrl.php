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

use Component\Database\DBTableField;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;

/**
 * Class GoogleDbUrl
 * @author  Sunny <bluesunh@godo.co.kr>
 */
class GoogleDbUrl extends \Component\Worker\AbstractDbUrl
{
    protected $header = ['id', 'title', 'link', 'image_link', 'condition', 'brand', 'shipping', 'description', 'availability', 'price', 'adult', 'product_type'];

    /**
     * init
     *
     * @throws \Exception
     */
    protected function init()
    {
        parent::init();
        $this->writeDbUrl(implode(chr(9), $this->header));
    }

    /**
     * DbUrl 정책 호출
     *
     */
    protected function loadConfig()
    {
        $componentDbUrl = \App::load('Component\\Marketing\\DBUrl');
        $this->googleConfig = $componentDbUrl->getConfig('google', 'config');
        $this->config['feedUseFl'] = $this->googleConfig['feedUseFl'];
    }

    /**
     * DbUrl 사용함 상태 확인
     *
     * @return bool
     */
    protected function notUseDbUrl(): bool
    {
        if (!key_exists('feedUseFl', $this->config)) {
            $this->loadConfig();
        }

        return $this->config['feedUseFl'] != 'y';
    }

    /**
     * 상품 시작과 종료 번호를 조회
     *
     * @param array $params
     *
     * @return array
     */
    protected function selectStartWithEndGoodsNo(array $params = []): array
    {
        $this->goodsWheres = [];
        $this->goodsWheres[] = 'g.goodsDisplayFl = \'y\'';
        $this->goodsWheres[] = 'g.delFl = \'n\'';
        $this->goodsWheres[] = 'g.applyFl = \'y\'';
        //$this->goodsWheres[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0)';
        //$this->goodsWheres[] = 'g.soldOutFl = \'n\'';
        $this->goodsWheres[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';
        $wheres = $this->goodsWheres;
        if (key_exists('where', $params) && count($params['where']) > 0) {
            $wheres = array_merge($wheres, $params['where']);
        }
        $strSQL = ' SELECT min(g.goodsNo) AS startGoodsNo,max(g.goodsNo) AS endGoodsNo FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $wheres);

        $db = \App::getInstance('DB');
        $resultSet = $db->query_fetch($strSQL, null, false);

        return $resultSet;
    }

    /**
     * countGoods
     *
     * @param array $params
     *
     * @return int
     */
    protected function countGoods(array $params = []): int
    {
        $this->goodsWheres = [];
        $this->goodsWheres[] = 'g.goodsDisplayFl = \'y\'';
        $this->goodsWheres[] = 'g.delFl = \'n\'';
        $this->goodsWheres[] = 'g.applyFl = \'y\'';
        //$this->goodsWheres[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0)';
        //$this->goodsWheres[] = 'g.soldOutFl = \'n\'';
        $this->goodsWheres[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';
        $wheres = $this->goodsWheres;
        if (key_exists('where', $params) && count($params['where']) > 0) {
            $wheres = array_merge($wheres, $params['where']);
        }
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $wheres);

        $db = \App::getInstance('DB');
        $resultSet = $db->query_fetch($strSQL, null, false);

        return StringUtils::strIsSet($resultSet['cnt'], 0);
    }

    /**
     * makeDbUrl
     *
     * @param \Generator $goodsGenerator
     * @param int        $pageNumber
     *
     * @return bool
     * @throws \Exception
     */
    protected function makeDbUrl(\Generator $goodsGenerator, int $pageNumber): bool
    {
        $this->totalDbUrlPage++;
        $goodsGenerator->rewind();
        while ($goodsGenerator->valid()) {
            if ($this->greaterThanMaxCount()) {
                break;
            }
            $goods = $goodsGenerator->current();
            $goodsGenerator->next();
            if ($goods['goodsNmPartner']) { // 제휴 상품
                $goods['goodsNm'] = $goods['goodsNmPartner'];
            }

            $cateListNm = [];
            $cateListKey = [];
            if ($goods['cateCd']) {
                if (empty($this->categoryStorage[$goods['cateCd']]) === true) {
                    $cateList = $this->componentCategory->getCategoriesPosition($goods['cateCd'])[0];
                    $this->categoryStorage[$goods['cateCd']] = $cateList;
                }
                $cateList = $this->categoryStorage[$goods['cateCd']];
                if ($cateList) {
                    $cateListNm = array_values($cateList);
                    $cateListKey = array_keys($cateList);
                }
                unset($cateList);
            }

            $this->selectCategoryBrand($goods);
            $goods['brandNm'] = $this->brandStorage[$goods['brandCd']];
            $deliveryPrice = $this->setDeliveryPrice($goods);

            $goodsImages = $this->getGoodsImageSrcWithAddImageSrc($goods);
            $goodsImageSrc = $goodsImages[0];
            $goodsAddImageSrc = $goodsImages[1];
            $onlyAdultFl = $this->getOnlyAdultFl($goods);

            $result = '';
            $result .= $goods['goodsNo'] . chr(9); // 상품코드 - id
            $result .= StringUtils::htmlSpecialCharsStripSlashes($goods['goodsNm']) . chr(9); // 상품명 - title
            $result .= 'http://' . $this->policy['basic']['info']['mallDomain'] . '/goods/goods_view.php?goodsNo=' . $goods['goodsNo'] . chr(9); // 상품 상세 url - link
            $result .= $goodsImageSrc . chr(9); // 상세 이미지 링크 - image_link
            // 조건 - condition
            // new – 상품상태 ’신상품, 반품, 전시, 스크래치‘ 또는 선택없음
            // refurbished – 상품상태 ‘리퍼’
            // used – 상품상태 ‘중고’
            switch($goods['goodsState']) {
                case 'u': // 중고
                    $result .= 'used' . chr(9);
                    break;
                case 'f': // 리퍼
                    $result .= 'refurbished' . chr(9);
                    break;
                default: // 신상품 외
                    $result .= 'new' . chr(9);
                    break;
            }
            $result .= StringUtils::strIsSet($goods['brandNm']) . chr(9); // 브랜드 - brand
            $result .= 'KR:::' . StringUtils::strIsSet($deliveryPrice) . '.00 KRW' . chr(9); // [선택] 배송비 (국가코드:지역:서비스:가격)- shipping
            $result .= '"' . str_replace('"','""',StringUtils::htmlSpecialCharsStripSlashes($goods['shortDescription'])) . '"' . chr(9); // 짧은 설명 - description
            if ($goods['soldOutFl'] == 'y') { // 재고 - availability
                $result .= 'out of stock' . chr(9); // 재고: 품절 표시
            } else {
                if ($goods['stockFl'] == 'n') {
                    $result .= 'in stock' . chr(9); // 재고: 재고 있음 (재고 무제한)
                } else if ($goods['totalStock'] > 0) {
                    $result .= 'in stock' . chr(9); // 재고: 재고 있음 (재고 1 이상)
                } else {
                    $result .= 'out of stock' . chr(9); // 재고: 재고 없음
                }
            }
            $result .= NumberUtils::moneyFormat($goods['goodsPrice'], false) . '.00 KRW' . chr(9); // 판매가 - price
            $result .= $onlyAdultFl . chr(9); // 성인상품여부 - adult
            $result .= @implode('>', $cateListNm); // 카테고리 - product_type

            $this->writeDbUrl($result);
            $this->totalDbUrlData++;
        }

        return true;
    }

    /**
     * 이미지 경로, 추가 이미지 경로 반환
     *
     * @param array $goods
     *
     * @return array
     * @throws \Exception
     */
    protected function getGoodsImageSrcWithAddImageSrc($goods)
    {
        $addImage = [];
        $addImageSrc = $imageSrc = "";
        if ($goods['imageName']) {
            $names = explode(STR_DIVISION, $goods['imageName']);
            $kinds = explode(STR_DIVISION, $goods['imageKind']);
            $numbers = explode(STR_DIVISION, $goods['imageNo']);

            foreach ($names as $imageNameKey => $imageName) {
                $image = $this->getGoodsImage($imageName, $goods['imagePath'], $goods['imageStorage']);
                if ($kinds[$imageNameKey] == 'magnify' && $numbers[$imageNameKey] == 0) {
                    $imageSrc = $image;
                } else {
                    $addImage[] = $image;
                }
                unset($image);
            }
            unset($names, $kinds, $numbers);

            if (empty($imageSrc) === true) {
                $imageSrc = $addImage[0];
                unset($addImage[0]);
            }

            if ($addImage) {
                $addImageSrc = implode("|", array_slice($addImage, 0, 10));
            }
        }

        unset($addImage);

        return [
            $imageSrc,
            $addImageSrc,
        ];
    }

    /**
     * getOnlyAdultFl
     *
     * @param array $goods
     *
     * @return string
     */
    protected function getOnlyAdultFl(array $goods)
    {
        $flag = "no";
        if ($goods['onlyAdultFl'] == 'y') {
            $flag = "yes";
        }

        return $flag;
    }
}