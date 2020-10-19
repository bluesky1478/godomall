<?php
/**
 *
 *  This is commercial software, only users who have purchased a valid license
 *  and accept to the terms of the License Agreement can install and use this
 *  program.
 *
 *  Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 *  versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 *
 */

namespace Bundle\Component\MemberStatistics;

/**
 * Class MileageVO
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class MileageVO extends \Component\MemberStatistics\StatisticsVO
{
    protected $memNo, $memId, $memNm, $mileage, $addCount, $addMileage, $removeCount, $removeMileage, $deleteScheduleMileage;

    /**
     * setArrData
     *
     * @param array $list
     */
    public function setArrData(array $list)
    {
        $this->total++;
        $vo = new MileageVO($list);

        $this->mileage += $vo->getMileage();
        $this->addCount += $vo->getAddCount();
        $this->addMileage += $vo->getAddMileage();
        $this->removeCount += $vo->getRemoveCount();
        $this->removeMileage += $vo->getRemoveMileage();
        $this->deleteScheduleMileage += $vo->getDeleteScheduleMileage();

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
    public function getMileage()
    {
        return $this->mileage;
    }

    /**
     * @param mixed $mileage
     */
    public function setMileage($mileage)
    {
        $this->mileage = $mileage;
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
    public function getAddMileage()
    {
        return $this->addMileage;
    }

    /**
     * @param mixed $addMileage
     */
    public function setAddMileage($addMileage)
    {
        $this->addMileage = $addMileage;
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
    public function getRemoveMileage()
    {
        return $this->removeMileage;
    }

    /**
     * @param mixed $removeMileage
     */
    public function setRemoveMileage($removeMileage)
    {
        $this->removeMileage = $removeMileage;
    }

    /**
     * @return mixed
     */
    public function getDeleteScheduleMileage()
    {
        return $this->deleteScheduleMileage;
    }

    /**
     * @param mixed $deleteScheduleMileage
     */
    public function setDeleteScheduleMileage($deleteScheduleMileage)
    {
        $this->deleteScheduleMileage = $deleteScheduleMileage;
    }
}
