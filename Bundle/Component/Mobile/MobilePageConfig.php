<?php
/**
 * 모바일 페이지 설정
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Mobile;

class MobilePageConfig
{
    const ERROR_VIEW                = 'ERROR_VIEW';

    protected $db;

    public $arrFields            = [];
    public $arrData            = [];
    public $arrChecked            = [];

    /**
     * 생성자
     */
    public function __construct()
    {
    }

    /**
     * 각 페이지 설정값 호출
     *
     * @param string $pageName 페이지 이름
     */
    public function setConfig($pageName, $pageConfigData = null)
    {
        // data
        $method    = 'get_page_config_' . $pageName;
        $this->arrFields    = $this->$method();

        // 각 페이지 설정값 체크
        foreach ($this->arrFields as $key => $val) {
            $this->arrData[$val['CodeNm']]                                        = gd_isset($pageConfigData[$val['CodeNm']], $val['mustFl']);
            $this->arrChecked[$val['CodeNm']][$pageConfigData[$val['CodeNm']]]    = 'checked="checked"';
        }
    }

    /**
     * goods_view 페이지 설정값
     *
     * @return array 설정값
     */
    private function getPageConfigGoodsView()
    {
        $arrFields = [
            ['titleNm' => __('옵션가격'),            'CodeNm' => 'optionPriceFl',        'mustFl' => 'y',        'desc' => __('옵션가격 출력 여부 - ● 표시할 경우 옵션의 가격이 표시됩니다.')],
            ['titleNm' => __('옵션가격 전부'),        'CodeNm' => 'optionPriceDiffFl',    'mustFl' => 'y',        'desc' => __('옵션가격 전부 출력 여부 - ● 옵션가격 출력 여부가 표시(사용)일 경우만 동작.<br />● O : 옵션가격 전부 출력.<br />● X : 옵션가격중 기본 가격과 다른 경우에만 출력.')],
            ['titleNm' => __('추가옵션가격'),        'CodeNm' => 'addOptionPriceFl',        'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('텍스트옵션가격'),    'CodeNm' => 'textOptionPriceFl',    'mustFl' => 'n',        'desc' => ''],
            ['titleNm' => __('상품번호'),            'CodeNm' => 'goodsNoFl',            'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('배송비 설명'),        'CodeNm' => 'deliveryFl',            'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('남은재고'),            'CodeNm' => 'stockFl',                'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('과세/비과세'),        'CodeNm' => 'taxFreeFl',            'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('상품코드'),            'CodeNm' => 'goodsCdFl',            'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('모델명'),            'CodeNm' => 'modelNoFl',            'mustFl' => 'n',        'desc' => ''],
            ['titleNm' => __('브랜드'),            'CodeNm' => 'brandFl',                'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('제조사'),            'CodeNm' => 'makerFl',                'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('원산지'),            'CodeNm' => 'originFl',                'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('상품무게'),            'CodeNm' => 'weightFl',                'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('추가항목'),            'CodeNm' => 'addInfoFl',            'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('아이콘'),            'CodeNm' => 'iconFl',                'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('제조일'),            'CodeNm' => 'makeYmdFl',            'mustFl' => 'n',        'desc' => ''],
            ['titleNm' => __('출시일'),            'CodeNm' => 'launchYmdFl',            'mustFl' => 'n',        'desc' => ''],
        ];

        return $arrFields;
    }

    /**
     * goods_search 페이지 설정값
     *
     * @return array 설정값
     */
    private function getPageConfigGoodsSearch()
    {
        $arrFields = [
            ['titleNm' => __('품절 상품'),            'CodeNm' => 'soldOutFl',            'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('품절 아이콘'),        'CodeNm' => 'soldOutIconFl',        'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('상품 이미지'),        'CodeNm' => 'imageFl',                'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('아이콘'),            'CodeNm' => 'iconFl',                'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('상품명'),            'CodeNm' => 'goodsNmFl',            'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('가격'),                'CodeNm' => 'goodsPriceFl',            'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('정가'),                'CodeNm' => 'fixedPriceFl',            'mustFl' => 'n',        'desc' => ''],
            ['titleNm' => __('마일리지'),            'CodeNm' => 'mileageFl',            'mustFl' => 'y',        'desc' => ''],
            ['titleNm' => __('브랜드'),            'CodeNm' => 'brandFl',                'mustFl' => 'n',        'desc' => ''],
            ['titleNm' => __('제조사'),            'CodeNm' => 'makerFl',                'mustFl' => 'n',        'desc' => ''],
            ['titleNm' => __('짧은설명'),            'CodeNm' => 'shortDescFl',            'mustFl' => 'n',        'desc' => ''],
        ];

        return $arrFields;
    }
}
