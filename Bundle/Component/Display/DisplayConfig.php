<?php
/**
 * 상품노출형태 관리
 * @author atomyang
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Display;

use Component\Storage\Storage;
use Component\Database\DBTableField;
use Globals;
use LogHandler;
use Request;

class DisplayConfig
{
    // __('메인테마')
    // __('카테고리테마')
    // __('브랜드테마')
    // __('검색페이지테마')
    // __('추천상품테마')
    // __('기획전테마')
    public $themeCategory = array(
        'B'=> '메인테마',
        'E'=> '카테고리테마',
        'C'=> '브랜드테마',
        'A'=> '검색페이지테마',
        'D'=> '추천상품테마',
        'F'=> '기획전테마'
    );

    // __('이미지')
    // __('브랜드')
    // __('제조사')
    // __('상품명')
    // __('짧은설명')
    // __('판매가')
    // __('할인적용가')
    // __('정가')
    // __('쿠폰가')
    // __('마일리지')
    // __('모델번호')
    // __('상품코드')
    public $themeDisplayField = array(
        'img'=> '이미지',
        'goodsColor' => '대표색상',
        'brandCd'=> '브랜드',
        'makerNm'=> '제조사',
        'goodsNm'=> '상품명',
        'shortDescription'=> '짧은설명',
        'fixedPrice'=> '정가',
        'goodsPrice'=> '판매가',
        'goodsDiscount'=> '할인적용가',
        'coupon'=> '쿠폰가',
        'goodsDcPrice'=> '상품할인금액',
        'mileage'=> '마일리지',
        'goodsModelNo'=> '모델번호',
        'goodsNo'=> '상품코드'
    );

    // __('상품할인가')
    // __('상품쿠폰할인가')
    public $themeGoodsDiscount = array(
        'goods' => '상품할인가',
        'coupon' => '상품쿠폰할인가',
    );

    // __('정가')
    // __('판매가')
    public $themePriceStrike = array(
        'fixedPrice' => '정가',
        'goodsPrice' => '판매가',
    );

    // __('할인율')
    public $themeDisplayAddField = array(
        'dcRate' => '할인율',
    );

    // __('갤러리형')
    // __('리스트형')
    // __('리스트그룹형')
    // __('상품이동형')
    // __('세로이동형')
    // __('스크롤형')
    // __('선택강조형')
    // __('심플이미지형')
    // __('말풍선형')
    // __('장바구니형')
    // __('탭진열형')
    // __('복수선택형')
    public $themeDisplayType = array(
        '01'=> array('name'=>'갤러리형','mobile'=>'y','class'=>'display_B display_E display_C display_A display_D display_F'),
        '02'=> array('name'=>'리스트형','mobile'=>'y','class'=>'display_B display_E display_C display_A display_D display_F'),
        '03'=> array('name'=>'리스트그룹형','class'=>'display_B display_E display_C display_D display_F'),
        '04'=> array('name'=>'상품이동형','mobile'=>'y','class'=>'display_B '),
        '05'=> array('name'=>'세로이동형','class'=>'display_B '),
        '06'=> array('name'=>'스크롤형','mobile'=>'y','class'=>'display_B '),
        '08'=> array('name'=>'선택강조형','class'=>'display_B display_E display_C display_D display_F'),
        '09'=> array('name'=>'심플이미지형','mobile'=>'y','class'=>'display_B display_E display_C display_D display_F'),
        '10'=> array('name'=>'말풍선형','class'=>'display_B display_E display_C display_D'),
        '11'=> array('name'=>'장바구니형','mobile'=>'y','class'=>'display_B display_E display_C display_D display_F'),
        '07'=> array('name'=>'탭진열형','mobile'=>'y','class'=>'display_B'),
        '12'=> array('name'=>'복수선택형','mobile'=>'y'),
    );

    protected $storage;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->storage = Storage::disk(Storage::PATH_CODE_GOODS_ICON);

    }

    /**
     * getInfoThemeConfig
     *
     * @param null $themeCd
     * @param null $giftField
     * @param null $arrBind
     * @param bool|false $dataArray
     * @return string
     */
    public function getInfoThemeConfig($themeCd = null, $giftField = null, $arrBind = null, $dataArray = false)
    {
        if ($themeCd) {
            if ($this->db->strWhere) {
                $this->db->strWhere    = " g.themeCd  = ? AND ".$this->db->strWhere;
            } else {
                $this->db->strWhere    = " g.themeCd  = ?";
            }
            $this->db->bind_param_push($arrBind, 's', $themeCd);
        }
        if ($giftField) {
            if ($this->db->strField) {
                $this->db->strField    = $giftField.', '.$this->db->strField;
            } else {
                $this->db->strField    = $giftField;
            }
        }
        $query    = $this->db->query_complete();
        $strSQL = 'SELECT '.array_shift($query).' FROM '.DB_DISPLAY_THEME_CONFIG.' g '.implode(' ', $query);
        $getData    = $this->db->query_fetch($strSQL, $arrBind);


        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getInfoThemeConfigCate
     *
     * @param $themeCd
     * @return bool|string
     */
    public function getInfoThemeConfigCate($themeCd,$mobileFl = 'n')
    {
        $strSQL = "SELECT * FROM " . DB_DISPLAY_THEME_CONFIG . " WHERE themeCate = ? and mobileFl = ? ORDER BY regDt ASC";
        $arrBind = array('ss', $themeCd, $mobileFl);

        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }


}

