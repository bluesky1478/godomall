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

namespace Bundle\Component\MemberStatistics;

/**
 * Class 회원 통계 데이터 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class StatisticsVO
{
    /**
     * @var int 총합
     */
    protected $total;
    /**
     * @var array 통계 데이터
     */
    protected $arrData = [];
    /**
     * @var \DateTime[] 검색조건
     */
    protected $searchDt;

    /**
     * @inheritDoc
     */
    public function __construct(array $array = null)
    {
        if (is_null($array) === false) {
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getArrData()
    {
        return $this->arrData;
    }

    /**
     * clearArrData
     *
     */
    public function clearArrData()
    {
        $this->arrData = [];
    }

    /**
     * add
     *
     * @param $variable
     * @param $value
     */
    public function add(&$variable, $value)
    {
        $variable += $value;
    }

    /**
     * setArray
     *
     * @param       $object
     * @param array $array
     */
    public function setArray(&$object, array $array)
    {
        foreach ($array as $index => $item) {
            $object->$index = $item;
        }
    }

    /**
     * addArray
     *
     * @param       $object
     * @param array $array
     */
    public function addArray(&$object, array $array)
    {
        foreach ($array as $index => $item) {
            $object->$index += $item;
        }
    }

    /**
     * @return \DateTime[]
     */
    public function getSearchDt()
    {
        return $this->searchDt;
    }

    /**
     * @param \DateTime[] $searchDt
     */
    public function setSearchDt($searchDt)
    {
        $this->searchDt = $searchDt;
    }
}
