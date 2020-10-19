<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Smart to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Marketing;

use App;
use Request;
use Component\Member\Util\MemberUtil;

class KakaoMomentCommonScript
{
    public $config;
    public $device;
    public $email;
    private $isEnabled = false;
    private $range = 'all';

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $dburl = App::load('\\Component\\Marketing\\DBUrl');
        $this->config = $dburl->getConfig('kakaoMoment', 'config');
        $this->isEnabled = $this->config['kakaoMomentFl'] == 'y';
        $this->range = $this->config['kakaoMomentRange'];
        $this->kakaoMomentScript = App::getConfig('outsidescript.kakaoMoment')->toArray();
        $this->device = Request::isMobile() === true ? 'mobile' : 'pc';
    }

    public function getKakaoMomentUseFl()
    {
        return $this->isEnabled === true && ($this->range == 'all' || $this->range == $this->device);
    }

    public function getCommonScript()
    {
        $script = '';
        if ($this->getKakaoMomentUseFl() === true) {
            $script = $this->kakaoMomentScript['common'];
            $script = str_replace('[KAKAO_MOMENT_CODE]', $this->config['kakaoMomentCode'], $script);
        }

        return $script;
    }

    public function getGoodsSearchScript($keyword = null)
    {
        $script = '';
        if($this->getKakaoMomentUseFl() ===  true && gd_isset($keyword)) {
            $script = $this->kakaoMomentScript['goodsSearch'];
            $script = str_replace(['[KAKAO_MOMENT_CODE]', '[SEARCH_KEYWORD]'], [$this->config['kakaoMomentCode'], $keyword], $script);
        }

        return $script;
    }

    public function getJoinOkScript()
    {
        $script = '';
        if ($this->getKakaoMomentUseFl() === true) {
            $script = $this->kakaoMomentScript['joinOk'];
            $script = str_replace('[KAKAO_MOMENT_CODE]', $this->config['kakaoMomentCode'], $script);
        }

        return $script;
    }

    public function getGoodsViewScript($goodsNo)
    {
        $script = '';
        if($this->getKakaoMomentUseFl() ===  true && gd_isset($goodsNo)) {
            $script = $this->kakaoMomentScript['goodsView'];
            $script = str_replace(['[KAKAO_MOMENT_CODE]', '[GOODS_NO]'], [$this->config['kakaoMomentCode'], $goodsNo], $script);
        }

        return $script;
    }

    public function getCartScript()
    {
        $script = '';
        if ($this->getKakaoMomentUseFl() === true) {
            $script = $this->kakaoMomentScript['cart'];
            $script = str_replace('[KAKAO_MOMENT_CODE]', $this->config['kakaoMomentCode'], $script);
        }

        return $script;
    }

    public function getOrderEndScript($orderInfo)
    {
        $script = '';
        if ($this->getKakaoMomentUseFl() === true) {
            $totalPrice = $orderInfo['settlePrice'] * 1;
            $goodsCnt = 0;
            $products = [];
            foreach ($orderInfo['goods'] as $goods) {
                $optNm = [];
                if (empty($goods['optionInfo']) === false) {
                    foreach ($goods['optionInfo'] as $optionInfo) {
                        $optNm[] = $optionInfo['optionValue'];
                    }
                }
                $price = empty($goods['goodsPriceString']) ? ($goods['goodsPrice'] + $goods['optionPrice'] + $goods['optionTextPrice']) * $goods['goodsCnt'] : $goods['goodsPriceString'];
                $products[] = "{ name: '" . $goods['goodsNm'] . ' ' . @implode(' ', $optNm) . "', quantity: '" . $goods['goodsCnt'] . "', price: '" . $price . "'}";
                $goodsCnt += $goods['goodsCnt'];
            }

            $script = $this->kakaoMomentScript['orderEnd'];
            $script = str_replace(['[KAKAO_MOMENT_CODE]', '[GOODS_CNT]', '[TOTAL_PRICE]', '[PRODUCTS]'], [$this->config['kakaoMomentCode'], $goodsCnt, $totalPrice, @implode(',', $products)], $script);
        }

        return $script;
    }
}
