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
namespace Bundle\Controller\Front\Service;

use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;

/**
 * 회사소개
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CompanyController extends \Controller\Front\Controller
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        $inform = new BuyerInform();
        $companyData = $inform->getInformData(BuyerInformCode::COMPANY);

        //--- 디자인 템플릿
        $this->setData('company', gd_isset($companyData['content']));
    }
}
