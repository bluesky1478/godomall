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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Member;

use Component\Member\Util\MemberUtil;
use DateTime;
use Framework\Utility\StringUtils;

/**
 * Class MemberVO
 * @package    Bundle\Component\Member
 * @author     yjwee
 * @deprecated 2016-12-05 위영종 사용을 권장하지 않습니다. VO 패턴을 이용하려면 직접 생성하는 것을 추천합니다.
 */
class MemberVO
{
    protected $memNo, $mallSno, $memId, $groupSno, $groupModDt, $groupValidDt, $memNm, $pronounceName, $nickNm, $memPw;
    protected $appFl, $memberFl, $entryBenefitOfferDt, $sexFl, $birthDt, $calendarFl, $email;
    protected $zipcode, $zonecode, $address, $addressSub, $phone, $cellPhone, $fax, $mileage, $deposit, $maillingFl, $smsFl;
    protected $marriFl;
    protected $marriDate;
    protected $job;
    protected $interest;
    protected $reEntryFl;
    protected $entryDt;
    protected $entryPath;
    protected $lastLoginDt;
    protected $lastLoginIp;
    protected $lastSaleDt;
    protected $loginCnt;
    protected $saleCnt;
    protected $saleAmt;
    protected $memo;
    protected $recommId;
    protected $recommFl;
    protected $ex1, $ex2, $ex3, $ex4, $ex5, $ex6;
    protected $privateApprovalFl;
    protected $privateApprovalOptionFl;
    protected $privateOfferFl;
    protected $privateConsignFl;
    protected $foreigner;
    protected $dupeinfo;
    protected $pakey;
    protected $rncheck;
    protected $adminMemo;
    protected $sleepFl;
    protected $sleepMailFl;
    protected $expirationFl;
    protected $regDt;
    protected $modDt;
    protected $company;
    protected $service;
    protected $item;
    protected $busiNo;
    protected $ceo;
    protected $companyZipcode;
    protected $companyZonecode;
    protected $companyAddress;
    protected $companyAddressSub;
    protected $under14ConsentFl;

    function __construct(array $arr = null)
    {
        if (is_null($arr) === false) {
            foreach ($arr as $key => $value) {
                $this->$key = $value;
            }
        }
        if (!isset($this->entryPath)) {
            $this->entryPath = \Request::isMobile() ? 'mobile' : 'pc';
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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $appFl
     */
    public function setAppFl($appFl)
    {
        $this->appFl = $appFl;
    }

    public function getAppFl()
    {
        return $this->appFl;
    }

    /**
     * @param bool $isDateTime
     *
     * @return mixed
     */
    public function getBirthDt($isDateTime = false)
    {
        if ($isDateTime) {

            return new DateTime($this->birthDt);
        } else {
            return $this->birthDt;
        }
    }

    /**
     * @param mixed $birthDt
     */
    public function setBirthDt($birthDt)
    {
        $this->birthDt = $birthDt;
    }

    /**
     * @return mixed
     */
    public function getCellPhone()
    {
        return $this->cellPhone;
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
    public function getDupeinfo()
    {
        return $this->dupeinfo;
    }

    /**
     * @param mixed $dupeinfo
     */
    public function setDupeinfo($dupeinfo)
    {
        $this->dupeinfo = $dupeinfo;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getBusiNo()
    {
        return $this->busiNo;
    }

    /**
     * @param bool $isDateTime
     *
     * @return string|DateTime
     */
    public function getEntryDt($isDateTime = false)
    {

        if ($isDateTime) {
            return new DateTime($this->entryDt);
        } else {
            return $this->entryDt;
        }
    }

    /**
     * @param mixed $entryDt
     */
    public function setEntryDt($entryDt)
    {
        if (empty($this->entryDt)) $this->entryDt = $entryDt;
    }

    /**
     * @return mixed
     */
    public function getEntryPath()
    {
        return $this->entryPath;
    }

    /**
     * @param mixed $entryPath
     */
    public function setEntryPath($entryPath)
    {
        $this->entryPath = $entryPath;
    }

    /**
     * @param mixed $expirationFl
     */
    public function setExpirationFl($expirationFl)
    {
        $this->expirationFl = $expirationFl;
    }

    /**
     * @param mixed $foreigner
     */
    public function setForeigner($foreigner)
    {
        $this->foreigner = $foreigner;
    }

    /**
     * @return mixed
     */
    public function getGroupSno()
    {
        return $this->groupSno;
    }

    /**
     * @param mixed $groupSno
     */
    public function setGroupSno($groupSno)
    {
        $this->groupSno = $groupSno;
    }

    /**
     * @return mixed
     */
    public function getMaillingFl()
    {
        return $this->maillingFl;
    }

    /**
     * @return mixed
     */
    public function getmarriFl()
    {
        return $this->marriFl;
    }

    /**
     * @return mixed
     */
    public function getMemberFl()
    {
        return $this->memberFl;
    }

    /**
     * @param mixed $memberFl
     */
    public function setMemberFl($memberFl)
    {
        $this->memberFl = $memberFl;
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
    public function getMemPw()
    {
        return $this->memPw;
    }

    /**
     * @param mixed $memPw
     */
    public function setMemPw($memPw)
    {
        $this->memPw = $memPw;
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
    public function getMemId()
    {
        return $this->memId;
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
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @param mixed $memo
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;
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
    public function getNickNm()
    {
        return $this->nickNm;
    }

    /**
     * @param mixed $nickNm
     */
    public function setNickNm($nickNm)
    {
        $this->nickNm = $nickNm;
    }

    /**
     * @return mixed
     */
    public function getPakey()
    {
        return $this->pakey;
    }

    /**
     * @param mixed $pakey
     */
    public function setPakey($pakey)
    {
        $this->pakey = $pakey;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getRecommId()
    {
        return $this->recommId;
    }

    /**
     * @param mixed $recommId
     */
    public function setRecommId($recommId)
    {
        $this->recommId = $recommId;
    }

    /**
     * @return mixed
     */
    public function getRecommFl()
    {
        return $this->recommFl;
    }

    /**
     * @param mixed $recommFl
     */
    public function setRecommFl($recommFl)
    {
        $this->recommFl = $recommFl;
    }

    /**
     * @return mixed
     */
    public function getReEntryFl()
    {
        return $this->reEntryFl;
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
     * @param mixed $rncheck
     */
    public function setRncheck($rncheck)
    {
        $this->rncheck = $rncheck;
    }

    /**
     * @return mixed
     */
    public function getSexFl()
    {
        return $this->sexFl;
    }

    /**
     * @return mixed
     */
    public function getSmsFl()
    {
        return $this->smsFl;
    }

    /**
     * @param mixed $privateApprovalFl
     */
    public function setPrivateApprovalFl($privateApprovalFl)
    {
        $this->privateApprovalFl = $privateApprovalFl;
    }

    /**
     * @param mixed $privateApprovalOptionFl
     */
    public function setPrivateApprovalOptionFl($privateApprovalOptionFl)
    {
        $this->privateApprovalOptionFl = $privateApprovalOptionFl;
    }

    /**
     * @param mixed $privateOfferFl
     */
    public function setPrivateOfferFl($privateOfferFl)
    {
        $this->privateOfferFl = $privateOfferFl;
    }

    /**
     * @param mixed $privateConsignFl
     */
    public function setPrivateConsignFl($privateConsignFl)
    {
        $this->privateConsignFl = $privateConsignFl;
    }

    /**
     * @return mixed
     */
    public function getMallSno()
    {
        return $this->mallSno;
    }

    /**
     * @param mixed $calendarFl
     */
    public function getCalendarFl()
    {
        return $this->calendarFl;
    }

    /**
     * @param mixed $under14ConsentFl
     */
    public function setUnder14ConsentFl($under14ConsentFl)
    {
        $this->under14ConsentFl = $under14ConsentFl;
    }

    /**
     * @param mixed $mallSno
     */
    public function setMallSno($mallSno)
    {
        $this->mallSno = $mallSno;
    }

    public function isset($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return isset($value) && is_null($value) === false && empty($value) === false;
    }

    public function databaseFormat()
    {
        $this->email = MemberUtil::emailFormatter($this->email);
        $this->phone = MemberUtil::phoneFormatter($this->phone);
        $this->cellPhone = MemberUtil::phoneFormatter($this->cellPhone);
        $this->fax = MemberUtil::phoneFormatter($this->fax);
        $this->busiNo = MemberUtil::businessNumberFormatter($this->busiNo);
        $this->interest = MemberUtil::extraFormatter($this->interest);
        $this->ex1 = MemberUtil::extraFormatter($this->ex1);
        $this->ex2 = MemberUtil::extraFormatter($this->ex2);
        $this->ex3 = MemberUtil::extraFormatter($this->ex3);
        $this->ex4 = MemberUtil::extraFormatter($this->ex4);
        $this->ex5 = MemberUtil::extraFormatter($this->ex5);
        $this->ex6 = MemberUtil::extraFormatter($this->ex6);
        $this->marriDate = MemberUtil::dateYmdDashFormatter($this->marriDate);
    }

    public function adminViewFormat()
    {
        // 사업자 번호가 DB에 저장될 때 - 가 없이 들어간 경우를 위한 체크
        if (strpos($this->busiNo, '-') == false) {
            $this->busiNo = StringUtils::numberToBusiness($this->busiNo);
        }
        $arrData = $this->toArray();
        foreach ($arrData as $index => $item) {
            if (strpos($item, '-') > -1 && $index === 'busiNo') {
                $this->$index = explode('-', $item);
            } else if (strpos($item, '@') > -1 && $index === 'email') {
                $this->$index = explode('@', $item);
            } else if (strpos($item, '|') > -1) {
                $this->$index = array_filter(explode('|', $item));
            }
        }

    }
}
