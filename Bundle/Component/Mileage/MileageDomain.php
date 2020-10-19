<?php
/**
 * *
 *  * This is commercial software, only users who have purchased a valid license
 *  * and accept to the terms of the License Agreement can install and use this
 *  * program.
 *  *
 *  * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 *  * versions in the future.
 *  *
 *  * @copyright ⓒ 2016, NHN godo: Corp.
 *  * @link http://www.godo.co.kr
 *
 */

namespace Bundle\Component\Mileage;

use Component\Code\Code;
use Session;

/**
 * Class MileageDomain
 * @package Bundle\Component\Mileage
 * @author  yjwee
 */
class MileageDomain
{
    const DELETE_Y = 'y';
    const DELETE_N = 'n';
    const DELETE_COMPLETE = 'complete';
    const DELETE_USE = 'use';

    private $sno;   // 일련번호
    private $memNo;    // 회원 번호
    private $managerId;    // 관리자 아이디
    private $managerNo;    // 관리자 키값
    private $handleMode;    // 처리 모드 (m - 회원, o - 주문, b - 게시판, r - 추천인, c - 쿠폰)
    private $handleCd;    // 처리 코드 (주문 번호, 게시판 코드)
    private $handleNo;    // 처리 번호 (상품 번호, 게시물 번호)
    private $beforeMileage;    // 이전 마일리지(지급/차감 전 마일리지)
    private $afterMileage;    // 이후 마일리지(지급/차감 후 마일리지)
    private $mileage;     // 마일리지(지급/차감)
    private $reasonCd;    // 지급/차감 사유 코드
    private $contents;    // 지급/차감 사유
    private $useHistory;    // 마일리지 사용 내역(마일리지 차감 {sno, mileage}, totalUseMileage)
    private $deleteFl;    // 소멸여부(y,n), 사용완료(complete), 사용중(use)
    private $deleteScheduleDt;    // 소멸예정일
    private $deleteDt;    // 소멸일
    private $regIp;     // 등록시 IP
    private $regDt;     // 등록일자

    function __construct(array $arr = null, $remoteAddress = null)
    {
        if ($remoteAddress == null) {
            // 마일리지 지급/차감 처리자 IP
            $this->regIp = \Request::getRemoteAddress();
        }

        // 관리자가 처리하는 경우
        if (Session::has('manager.managerId')) {
            $this->managerId = Session::get('manager.managerId');
            $this->managerNo = Session::get('manager.sno');

        }

        if (is_null($arr) === false) {
            foreach ($arr as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function toChecked()
    {
        $array = $this->toArray();
        $checked = [];
        foreach ($array as $index => $item) {
            $checked[$index][$item] = 'checked="checked"';
        }

        return $checked;
    }

    public function toArray()
    {
        return get_object_vars($this);
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
    public function getManagerId()
    {
        return $this->managerId;
    }

    /**
     * @param mixed $managerId
     */
    public function setManagerId($managerId)
    {
        $this->managerId = $managerId;
    }

    /**
     * @return mixed
     */
    public function getManagerNo()
    {
        return $this->managerNo;
    }

    /**
     * @param $managerNo
     * @internal param mixed $managerId
     */
    public function setManagerNo($managerNo)
    {
        $this->managerNo = $managerNo;
    }

    /**
     * @return mixed
     */
    public function getHandleMode()
    {
        return $this->handleMode;
    }

    /**
     * @param mixed $handleMode
     */
    public function setHandleMode($handleMode)
    {
        $this->handleMode = $handleMode;
    }

    /**
     * @return mixed
     */
    public function getHandleCd()
    {
        return $this->handleCd;
    }

    /**
     * @param mixed $handleCd
     */
    public function setHandleCd($handleCd)
    {
        $this->handleCd = $handleCd;
    }

    /**
     * @return mixed
     */
    public function getHandleNo()
    {
        return $this->handleNo;
    }

    /**
     * @param mixed $handleNo
     */
    public function setHandleNo($handleNo)
    {
        $this->handleNo = $handleNo;
    }

    /**
     * @return mixed
     */
    public function getBeforeMileage()
    {
        return $this->beforeMileage;
    }

    /**
     * @param mixed $beforeMileage
     */
    public function setBeforeMileage($beforeMileage)
    {
        $this->beforeMileage = $beforeMileage;
    }

    /**
     * @return mixed
     */
    public function getAfterMileage()
    {
        return $this->afterMileage;
    }

    /**
     * @param mixed $afterMileage
     */
    public function setAfterMileage($afterMileage)
    {
        $this->afterMileage = $afterMileage;
    }

    /**
     * getUseMileage
     *
     */
    public function getUseMileage()
    {
        $useMileage = 0;
        $arrUseHistory = json_decode($this->useHistory, true);

        if (count($arrUseHistory['use']) < 1) {
            return $useMileage;
        }
        foreach ($arrUseHistory['use'] as $index => $item) {
            $useMileage += $item['mileage'];
        }

        return $useMileage;
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
    public function getReasonCd()
    {
        return $this->reasonCd;
    }

    /**
     * @param mixed $reasonCd
     */
    public function setReasonCd($reasonCd)
    {
        $this->reasonCd = $reasonCd;

        // 적립 사유 기타(01005011)이 아닐 경우 코드관리의 내용을 입력
        if ($reasonCd !== Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ETC) {
            $mileageReasons = Code::getGroupItems('01005');
            $this->contents = $mileageReasons[$reasonCd];
        }
    }

    /**
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param mixed $contents
     */
    public function setContents($contents)
    {
        if ($this->reasonCd == Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ETC && is_null($contents) === false) {
            $this->contents = $contents;
        } elseif ($this->reasonCd == Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_GIFT_COUPON && is_null($contents) === false) {
            $this->contents = $contents;
        } elseif ($this->reasonCd == Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_USE_GOODS_BUY && is_null($contents) === false) {
            $this->contents = $contents;
        } elseif ($this->reasonCd == Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_REGISTER_RECOMMEND && is_null($contents) === false) {
            $this->contents = $contents;
        } elseif ($this->reasonCd == Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_RECEIVE_RECOMMEND && is_null($contents) === false) {
            $this->contents .= $contents;
        } elseif ($this->reasonCd == Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MILEAGE_SAVE_COUPON && is_null($contents) === false) {
            $this->contents .= $contents;
        }
    }

    /**
     * @return mixed
     */
    public function getUseHistory()
    {
        return $this->useHistory;
    }

    /**
     * @param mixed $useHistory
     */
    public function setUseHistory($useHistory)
    {
        $this->useHistory = $useHistory;
    }

    /**
     * pushUseHistory
     *
     * @param $useHistory
     */
    public function pushUseHistory($useHistory)
    {
        if (!is_array($this->useHistory)) {
            $this->useHistory = [];
        }
        $this->useHistory[] = $useHistory;
    }

    /**
     * @return mixed
     */
    public function getDeleteFl()
    {
        return $this->deleteFl;
    }

    /**
     * @param mixed $deleteFl
     */
    public function setDeleteFl($deleteFl)
    {
        $this->deleteFl = $deleteFl;
    }

    /**
     * @return mixed
     */
    public function getDeleteScheduleDt()
    {
        return $this->deleteScheduleDt;
    }

    /**
     * @param mixed $deleteScheduleDt
     */
    public function setDeleteScheduleDt($deleteScheduleDt)
    {
        $this->deleteScheduleDt = $deleteScheduleDt;
    }

    /**
     * @return mixed
     */
    public function getDeleteDt()
    {
        return $this->deleteDt;
    }

    /**
     * @param mixed $deleteDt
     */
    public function setDeleteDt($deleteDt)
    {
        $this->deleteDt = $deleteDt;
    }

    /**
     * @return mixed
     */
    public function getRegIp()
    {
        return $this->regIp;
    }

    /**
     * @param mixed $regIp
     */
    public function setRegIp($regIp)
    {
        $this->regIp = $regIp;
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
}
