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
namespace Bundle\Component\GoodsStatistics;

/**
 * Class 상품분석-검색어순위분석 ValueObject
 * @package Bundle\Component\GoodsStatistics
 * @author  yjwee
 */
class SearchWordStatisticsVO
{
    private $sno;
    private $mallSno;
    private $keyword;
    private $resultCount;
    private $os;
    private $regDt;
    private $modDt;
    private $keywordCount;
    private $keywordRate;

    /**
     * SearchWordStatisticsVO constructor.
     *
     * @param array|null $arr 배열의 키값에 해당하는 변수에 값을 대입한다.
     */
    public function __construct(array $arr = null)
    {
        if (is_null($arr) === false) {
            foreach ($arr as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getSno()
    {
        return $this->sno;
    }

    /**
     * @param mixed $sno
     */
    public function setSno($sno)
    {
        $this->sno = $sno;
    }

    /**
     * @return mixed
     */
    public function getMallSno()
    {
        return $this->mallSno;
    }

    /**
     * @param mixed $mallSno
     */
    public function setMallSno($mallSno)
    {
        $this->mallSno = $mallSno;
    }

    /**
     * @return mixed
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param mixed $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * @return mixed
     */
    public function getResultCount()
    {
        return $this->resultCount;
    }

    /**
     * @param mixed $resultCount
     */
    public function setResultCount($resultCount)
    {
        $this->resultCount = $resultCount;
    }

    /**
     * @return mixed
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * @param mixed $os
     */
    public function setOs($os)
    {
        $this->os = $os;
    }

    /**
     * @return mixed
     */
    public function getRegDt()
    {
        return $this->regDt;
    }

    /**
     * @param mixed $regDt
     */
    public function setRegDt($regDt)
    {
        $this->regDt = $regDt;
    }

    /**
     * @return mixed
     */
    public function getModDt()
    {
        return $this->modDt;
    }

    /**
     * @param mixed $modDt
     */
    public function setModDt($modDt)
    {
        $this->modDt = $modDt;
    }

    /**
     * @return mixed
     */
    public function getKeywordCount()
    {
        return $this->keywordCount;
    }

    /**
     * @param mixed $keywordCount
     */
    public function setKeywordCount($keywordCount)
    {
        $this->keywordCount = $keywordCount;
    }

    /**
     * @return mixed
     */
    public function getKeywordRate()
    {
        return $this->keywordRate;
    }

    /**
     * @param mixed $keywordRate
     */
    public function setKeywordRate($keywordRate)
    {
        $this->keywordRate = number_format($keywordRate, 2, '.', '');
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
