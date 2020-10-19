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

/**
 * Class TargetingGates
 * @package Bundle\Component\Marketing
 * @author  Hakyoung Lee <haky2@godo.co.kr>
 */
class TargetingGates
{
    public $config; // 세팅값
    private $isEnabled = false; // 타게팅게이츠 사용 여부
    private $targetingGatesScript;

    public function __construct()
    {
        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $this->config = $dbUrl->getConfig('targetingGates', 'config');
        $this->isEnabled = $this->config['tgFl'];
        $this->targetingGatesScript = App::getConfig('outsidescript.targetingGates')->toArray();
    }

    // 설정 값
    public function getConfig()
    {
        return $this->config;
    }

    // 사용 여부
    public function getTgUseCheck()
    {
        return $this->isEnabled;
    }

    // 공통 스크립트
    public function getTgCommonScript($isMobile)
    {
        if ($this->isEnabled === 'y') {
            if ($isMobile === true) {
                $tgCommonScript = $this->targetingGatesScript['commonMobile'];
            } else {
                $tgCommonScript = $this->targetingGatesScript['common'];
            }
            $tgCommonScript = str_replace('[tgCode]', $this->config['tgCode'], $tgCommonScript);
        }
        return $tgCommonScript;
    }

    // 상품상세 스크립트
    public function getTgGoodsScript($goodsNo, $goodsNm, $goodsPrice)
    {
        if ($this->isEnabled === 'y') {
            $tgGoodsScript = $this->targetingGatesScript['goodsView'];
            $tgGoodsScript = str_replace('[goodsNo]', $goodsNo, $tgGoodsScript);
            $tgGoodsScript = str_replace('[goodsNm]', $goodsNm, $tgGoodsScript);
            $tgGoodsScript = str_replace('[price]', $goodsPrice, $tgGoodsScript);
        }
        return $tgGoodsScript;
    }

    // 장바구니 스크립트
    public  function getTgCartScript($cartInfo)
    {
        if ($this->isEnabled === 'y') {
            $tgCartScript = '';
            $pageScript = $this->targetingGatesScript['cart'];
            foreach ($cartInfo as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $gKey => $gVal) {
                        $tgCartScript .= str_replace('[goodsNo]', $gVal['goodsNo'], $pageScript);
                    }
                }
            }
        }
        return $tgCartScript;
    }

    // 구매완료 스크립트
    public  function getTgOrderEndScript($orderNo, $price)
    {
        if ($this->isEnabled === 'y') {
            $tgOrderEndScript = $this->targetingGatesScript['orderEnd'];
            $tgOrderEndScript = str_replace('[orderNo]', $orderNo, $tgOrderEndScript);
            $tgOrderEndScript = str_replace('[price]', $price, $tgOrderEndScript);
        }
        return $tgOrderEndScript;
    }
}