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
 * Class DepositVO
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class DepositVO extends \Component\MemberStatistics\StatisticsVO
{
    protected $memNo, $memId, $memNm, $deposit, $addCount, $addDeposit, $removeCount, $removeDeposit;

    /**
     * setArrData
     *
     * @param array $list
     */
    public function setArrData(array $list)
    {
        $this->total++;
        $vo = new DepositVO($list);

        $this->deposit += $vo->getDeposit();
        $this->addCount += $vo->getAddCount();
        $this->addDeposit += $vo->getAddDeposit();
        $this->removeCount += $vo->getRemoveCount();
        $this->removeDeposit += $vo->getRemoveDeposit();

        $this->arrData[$vo->getMemNo()] = $vo;
    }

    /**
     * @return mixed
     */
    public function getMemNo()
    {
        return $this->memNo;
    }

    /**
     * @param mixed $memNo
     */
    public function setMemNo($memNo)
    {
        $this->memNo = $memNo;
    }

    /**
     * @return mixed
     */
    public function getMemId()
    {
        return $this->memId;
    }

    /**
     * @param mixed $memId
     */
    public function setMemId($memId)
    {
        $this->memId = $memId;
    }

    /**
     * @return mixed
     */
    public function getMemNm()
    {
        return $this->memNm;
    }

    /**
     * @param mixed $memNm
     */
    public function setMemNm($memNm)
    {
        $this->memNm = $memNm;
    }

    /**
     * @return mixed
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @param mixed $deposit
     */
    public function setDeposit($deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * @return mixed
     */
    public function getAddCount()
    {
        return $this->addCount;
    }

    /**
     * @param mixed $addCount
     */
    public function setAddCount($addCount)
    {
        $this->addCount = $addCount;
    }

    /**
     * @return mixed
     */
    public function getAddDeposit()
    {
        return $this->addDeposit;
    }

    /**
     * @param mixed $addDeposit
     */
    public function setAddDeposit($addDeposit)
    {
        $this->addDeposit = $addDeposit;
    }

    /**
     * @return mixed
     */
    public function getRemoveCount()
    {
        return $this->removeCount;
    }

    /**
     * @param mixed $removeCount
     */
    public function setRemoveCount($removeCount)
    {
        $this->removeCount = $removeCount;
    }

    /**
     * @return mixed
     */
    public function getRemoveDeposit()
    {
        return $this->removeDeposit;
    }

    /**
     * @param mixed $removeDeposit
     */
    public function setRemoveDeposit($removeDeposit)
    {
        $this->removeDeposit = $removeDeposit;
    }
}
