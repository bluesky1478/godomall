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

use Component\Mall\Mall;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Framework\Utility\ArrayUtils;
use Request;

/**
 * Class 약관 전체 보기
 * @package Bundle\Controller\Front\Service
 * @author  yjwee
 */
class AgreementController extends \Controller\Front\Controller
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        $terms = null;
        $inform = new BuyerInform();
        $mallSno = gd_isset(Mall::getSession('sno'), DEFAULT_MALL_NUMBER);

        $agreementCode = Request::get()->get('code', BuyerInformCode::AGREEMENT);
        switch (BuyerInformCode::removeCodeSuffix($agreementCode)) {
            case BuyerInformCode::AGREEMENT:
                $terms = $inform->getAgreementWithReplaceCode($agreementCode);
                $informList = $inform->makeSelectBoxByInform($terms['informCd'], $mallSno);
                $terms['content'] .= $informList;
                break;
            case BuyerInformCode::PRIVATE_APPROVAL_OPTION:
                $terms = $inform->getInformDataArray(BuyerInformCode::PRIVATE_APPROVAL_OPTION);
                $terms['content'] = ArrayUtils::keyImplode('content', '<br><br>', $terms);
                break;
            case BuyerInformCode::PRIVATE_APPROVAL:
                $terms = $inform->getInformData(BuyerInformCode::PRIVATE_APPROVAL);
                break;
            case BuyerInformCode::BASE_PRIVATE:
                $terms = $inform->getPrivateWithReplaceCode(BuyerInformCode::BASE_PRIVATE);
                break;
            case BuyerInformCode::PRIVATE_CONSIGN:
                $terms = $inform->getInformDataArray(BuyerInformCode::PRIVATE_CONSIGN);
                $terms['content'] = ArrayUtils::keyImplode('content', '<br><br>', $terms);

                break;
            case BuyerInformCode::PRIVATE_OFFER:
                $terms = $inform->getInformDataArray(BuyerInformCode::PRIVATE_OFFER);
                $terms['content'] = ArrayUtils::keyImplode('content', '<br><br>', $terms);

                break;
            case BuyerInformCode::PRIVATE_PROVIDER:
                $terms = $inform->getInformData(BuyerInformCode::PRIVATE_PROVIDER);
                break;
            case BuyerInformCode::PRIVATE_GUEST_COMMENT_WRITE:
                $terms = $inform->getInformData(BuyerInformCode::PRIVATE_GUEST_COMMENT_WRITE);
                break;
        }
        $this->setData('codeData', BuyerInformCode::toKeyArray($agreementCode));
        $this->setData('terms', $terms);
    }
}
