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
 * Class 전체 회원 통계 데이터 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class MemberVO extends \Component\MemberStatistics\StatisticsVO
{
    /**
     * @var string|\DateTime 회원가입일
     */
    protected $entryDt;


    /**
     * @return mixed
     */
    public function getEntryDt()
    {
        return $this->entryDt;
    }

    /**
     * @param mixed $entryDt
     */
    public function setEntryDt($entryDt)
    {
        $this->entryDt = $entryDt;
    }
}
