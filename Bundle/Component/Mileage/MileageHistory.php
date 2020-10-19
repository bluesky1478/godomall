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

namespace Bundle\Component\Mileage;

use Component\AbstractComponent;
use Component\Database\DBTableField;

/**
 * Class MileageHistory
 * @package Bundle\Component\Mileage
 * @author  yjwee
 */
class MileageHistory extends \Component\AbstractComponent
{
    /** @var  MileageDAO */
    private $dao;
    /** @var  Mileage */
    private $mileageService;
    /** @var  MileageDomain */
    private $mileageDomain;
    private $useMileage;
    private $usableMemberMileage;
    private $fieldTypes;
    private $targetMileage;
    private $mileageLeft;

    public function __construct(MileageDAO $dao = null, Mileage $mileageService = null)
    {
        if ($dao === null) {
            $dao = new MileageDAO();
        }
        $this->dao = $dao;
        if ($mileageService === null) {
            $mileageService = new Mileage();
        }
        $this->mileageService = $mileageService;

        parent::__construct();
    }

    public function setMileageDomain(MileageDomain $domain)
    {
        $this->mileageDomain = $domain;
    }

    public function saveUseHistory()
    {
        $this->usableMemberMileage = $this->dao->getUsableMemberMileage($this->mileageDomain->getMemNo());

        /**
         * 사용가능한 마일리지를 선지급된 순서대로 사용완료 또는 사용중 처리
         */
        $this->useMileage = [];
        $this->targetMileage = $this->mileageDomain->getMileage() * -1; // 차감 될 마일리지를 양수로 변환
        $idx = 0;

        $usableCount = count($this->usableMemberMileage);
        while ($this->targetMileage > 0 && $usableCount > 0) {
            $usableMileage = $this->usableMemberMileage[$idx];
            if (is_null($usableMileage)) {  // null 인 경우 지급된 마일리지에서 더 이상 차감할 수 없기 때문에 종료
                break;
            }
            $this->addUseMileage($usableMileage);
            $this->targetMileage = $this->targetMileage - $this->mileageLeft;
            $idx++;
        }
        $this->fieldTypes = DBTableField::getFieldTypes('tableMemberMileage');
        $this->fieldTypes['sno'] = 'i';

        $this->updateUseMileage();
    }

    public function addUseMileage(array $usableMileage)
    {
        $domain = new MileageDomain($usableMileage, $this->remoteAddress);
        $domain->setDeleteFl(MileageDomain::DELETE_COMPLETE);   // 마일리지 사용완료
        $this->mileageLeft = $domain->getMileage() - $domain->getUseMileage();    // 잔여 마일리지
        $arrUse = [
            'sno'     => $this->mileageDomain->getSno(),
            'mileage' => is_int($this->mileageLeft) ? number_format((float) $this->mileageLeft, 2, '.', '') : $this->mileageLeft,
        ];

        $arrUseHistory = json_decode($domain->getUseHistory(), true);
        $arrUseHistory['totalUseMileage'] = $domain->getMileage();

        // 지급건의 마일리지가 사용된 마일리보다 클 경우 마일리지 사용중 상태로 변경
        if ($this->targetMileage < $this->mileageLeft) {
            $domain->setDeleteFl(MileageDomain::DELETE_USE);    // 마일리지 사용
            $arrUse['mileage'] = $this->targetMileage;
            $arrUseHistory['totalUseMileage'] = $domain->getUseMileage() + $this->targetMileage;  // 총 사용 마일리지
        }
        // 기존 저장된 마일리지 사용 내역 존재 여부
        if (count($arrUseHistory) < 1) {
            $arrUseHistory = ['use' => [$arrUse]];
        } else {
            $arrUseHistory['use'][] = $arrUse;
        }
        $domain->setUseHistory(json_encode($arrUseHistory));
        $this->useMileage[] = $domain;
    }

    public function updateUseMileage()
    {
        /** @var \Bundle\Component\Mileage\MileageDomain $domain */
        foreach ($this->useMileage as $domain) {
            $this->dao->updateUseHistoryWithDeleteFlag($domain);
        }
    }

    /**
     * @return mixed
     */
    public function getUseMileage()
    {
        return $this->useMileage;
    }

    /**
     * @param mixed $targetMileage
     */
    public function setTargetMileage($targetMileage)
    {
        $this->targetMileage = $targetMileage;
    }

    /**
     * @param mixed $mileageLeft
     */
    public function setMileageLeft($mileageLeft)
    {
        $this->mileageLeft = $mileageLeft;
    }

}
