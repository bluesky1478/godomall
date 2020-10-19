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

namespace Bundle\Component\Policy;

use Component\Storage\Storage;
use Component\Validator\Validator;
use Framework\Utility\ImageUtils;

/**
 * 약관/개인정보취급방침 정책
 * @package Bundle\Component\Policy
 * @author  yjwee
 */
class BaseAgreementPolicy extends \Component\Policy\Policy
{
    const KEY_FAIR_TRADE = 'basic.fairTrade';
    const KEY_AGREEMENT = 'basic.agreement';
    protected $requestFiles;

    public function getAgreementDate()
    {
        $agreementDate = $this->getValue(self::KEY_AGREEMENT);

        return $agreementDate;
    }

    public function saveAgreementDate()
    {
        $this->fillAgreementDate();
        $useStandard = \Request::post()->get('agreementModeFl') == 'y';
        if ($useStandard && ($this->hasAgreementDate() === false)) {
            throw new \Exception(__('약관적용일 날짜를 정확하게 입력해주시기 바랍니다.'));
        }
        $agreementDate = \Request::post()->get('agreementDate');
        gd_set_policy(self::KEY_AGREEMENT, $agreementDate);
    }

    protected function fillAgreementDate()
    {
        $date = \Request::post()->get('agreementDate');
        if (empty($date['month']) === false) {
            $date['month'] = str_pad($date['month'], 2, '0', STR_PAD_LEFT);
        }
        if (empty($date['day']) === false) {
            $date['day'] = str_pad($date['day'], 2, '0', STR_PAD_LEFT);
        }
        \Request::post()->set('agreementDate', $date);
    }

    protected function hasAgreementDate()
    {
        try {
            $agreementDate = \Request::post()->get('agreementDate');
            $year = $agreementDate['year'];
            $month = $agreementDate['month'];
            $day = $agreementDate['day'];
            $isEmpty = empty($year) && empty($month) && empty($day);
            $isDate = Validator::date($year . '-' . $month . '-' . $day, true) || $isEmpty;
        } catch (\Exception $e) {
            $isDate = false;
        }

        return $isDate;
    }

    public function setFairTradeLogo($arrData)
    {
        if ($this->isUploadLogo($arrData)) {
            if ($arrData['logoUploadFile'] == '') {
                throw new \Exception(__('이미지를 등록해주세요.'));
            }
            if ($this->isPossibleUploadImage()) {
                $this->saveUpload($arrData);
            } else {
                $this->saveBackup($arrData);
            }
        } else {
            $this->saveDefault($arrData);
        }
    }

    protected function isUploadLogo(array $arrData)
    {
        return $arrData['logoFl'] == 'upload' && $arrData['uploadDeleteFl'] != 'y';
    }

    protected function isPossibleUploadImage()
    {
        return ImageUtils::isFileUploadable($this->requestFiles['logoUploadFile'], 'image', []) === true;
    }

    protected function saveUpload(array $arrData)
    {
        $logoPath = $this->getLogoPath();
        $tmpImageFile = $this->requestFiles['logoUploadFile']['tmp_name'];
        list($tmpSize['width'], $tmpSize['height']) = getimagesize($tmpImageFile);
        Storage::disk(Storage::PATH_CODE_COMMON, 'local')->upload($tmpImageFile, $logoPath);
        $arrData['logoUploadFile'] = $logoPath;
        unset($arrData['logoUploadFileTmp']);
        $this->setValue(self::KEY_FAIR_TRADE, $arrData);
    }

    protected function getLogoPath()
    {
        return 'fairTradeUploadLogo.' . pathinfo($this->requestFiles['logoUploadFile']['name'], PATHINFO_EXTENSION);
    }

    protected function saveBackup(array $arrData)
    {
        $arrData['logoUploadFile'] = '';
        if (!empty($arrData['logoUploadFileTmp'])) {
            $arrData['logoUploadFile'] = $arrData['logoUploadFileTmp'];
        }
        unset($arrData['logoUploadFileTmp']);
        $this->setValue(self::KEY_FAIR_TRADE, $arrData);
    }

    /**
     * saveDefault
     *
     * @param $arrData
     */
    protected function saveDefault($arrData)
    {
        $arrData['logoUploadFile'] = '';
        if (!empty($arrData['logoUploadFileTmp'])) {
            $arrData['logoUploadFile'] = $arrData['logoUploadFileTmp'];
        }
        $policy = [
            'logoFl'         => $arrData['logoFl'],
            'logoUploadFile' => $arrData['logoUploadFile'],
            'uploadDeleteFl' => $arrData['uploadDeleteFl'],
        ];
        $this->setValue(self::KEY_FAIR_TRADE, $policy);
    }

    /**
     * @param mixed $requestFiles
     */
    public function setRequestFiles($requestFiles)
    {
        $this->requestFiles = $requestFiles;
    }

}
