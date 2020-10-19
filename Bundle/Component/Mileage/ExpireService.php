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

/**
 * Class ExpireService
 * @package Bundle\Component\Mileage
 * @author  yjwee
 * @deprecated 2017-03-27 yjwee 스케줄러 실행의 안정성 확보를 위해 로직을 스케줄러로 이동
 */
class ExpireService
{
    private $dao;
    private $expireDate;
    /** @var Mileage */
    private $mileageService;

    /**
     * @inheritDoc
     */
    public function __construct(MileageDAO $dao = null, Mileage $mileageService = null, $date = null)
    {
        if ($dao === null) {
            $dao = new MileageDAO();
        }
        $this->dao = $dao;

        if ($mileageService === null) {
            $mileageService = new Mileage();
        }
        $this->mileageService = $mileageService;

        if ($date) {
            $this->expireDate = $date;
        } else {
            $this->expireDate = new \DateTime();
            $this->expireDate = $this->expireDate->format('Y-m-d');;
        }
    }

    /**
     * expire
     *
     * @return array
     * @deprecated 2017-11-20 yjwee
     */
    public function expire()
    {
        $lists = $this->dao->getListsByExpireDate($this->expireDate);

        $results = [];
        foreach ($lists as $list) {
            $list['mileage'] = MileageUtil::removeUseHistory($list['mileage'], $list['useHistory']);
            $list['mileage'] = (abs($list['mileage']) * -1);
            $result = $this->mileageService->setMemberMileage($list['memNo'], $list['mileage'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_EXPIRE, 'm', null, null, Mileage::REASON_TEXT_EXPIRE);

            $results[] = [
                $list['memNo'] => [
                    $result,
                    $list['mileage'],
                ],
            ];
        }

        return $results;
    }

}
