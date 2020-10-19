<?php


namespace Bundle\Component\Marketing;


/**
 * Marketing 관련 define 정보 담은 클래스
 *
 * @package Bundle\Component\Marketing
 * @author  yido <sf2000@godo.co.kr>
 */
class DefineMarketing
{
    public $naverGrade = [];
    public $naverGradeMaxCount = [];
    public $naverGradeSafetyCount = [];

    public function __construct()
    {
        //회원등급
        $this->naverGrade = [
            '1' => __('씨앗'),
            '2' => __('새싹'),
            '3' => __('파워'),
            '4' => __('빅파워'),
            '5' => __('프리미엄'),
        ];

        //등급에 따른 생성 상품최대수
        $this->naverGradeMaxCount = [
            '1' => __('10000'),
            '2' => __('10000'),
            '3' => __('50000'),
            '4' => __('100000'),
            '5' => __('500000'),
        ];

        //생성 상품 안전 수
        $this->naverGradeSafetyCount = [
            '1' => __('100'),
            '2' => __('100'),
            '3' => __('100'),
            '4' => __('1000'),
            '5' => __('1000'),
        ];

    }


    public function getNaverGrade()
    {
        return $this->naverGrade;
    }

    public function getNaverGradeMaxCount()
    {
        return $this->naverGradeMaxCount;
    }

    public function getNaverGradeSafetyCount()
    {
        return $this->naverGradeSafetyCount;
    }

}
