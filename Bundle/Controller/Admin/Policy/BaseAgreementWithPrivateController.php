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

namespace Bundle\Controller\Admin\Policy;

use Component\Mall\Mall;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Component\Agreement\BuyerInformUtil;
use Component\Policy\BaseAgreementPolicy;
use Exception;
use Framework\Utility\StringUtils;
use Request;

/**
 * Class BaseAgreementWithPrivateController
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class BaseAgreementWithPrivateController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('policy', 'basic', 'agreementWithPrivate');

        try {
            $mall = new Mall();
            $mallSno = gd_isset(\Request::get()->get('mallSno'), 1);

            $mallList = $mall->getListByUseMall();
            if (count($mallList) > 1) {
                $this->setData('mallCnt', count($mallList));
                $this->setData('mallList', $mallList);
                $this->setData('mallSno', $mallSno);
            }

            $agreementWithPrivateUtil = new BuyerInformUtil();
            $buyerInform = new BuyerInform();

            $mode = Request::get()->get('mode', 'agreement');

            $this->setData('mode', $mode);
            switch ($mode) {
                case 'agreement':
                    $display = '';
                    $agreementData = $buyerInform->getAgreementWithChecked($mallSno);
                    $fairTrade = gd_policy(BaseAgreementPolicy::KEY_FAIR_TRADE, $mallSno);
                    gd_isset($fairTrade['logoFl'], 'no');
                    $agreementData['checked']['logoFl'][$fairTrade['logoFl']] = 'checked="checked"';
                    $agreementDate = gd_policy(BaseAgreementPolicy::KEY_AGREEMENT, $mallSno);
                    $data = array_merge($fairTrade, ['agreementDate' => $agreementDate]);
                    if ($mallSno > DEFAULT_MALL_NUMBER) {
                        $display = 'display-none';
                    }
                    $this->setData('data', $data);
                    $this->setData('display', $display);
                    $this->setData('agreement', $agreementData);
                    $this->setData('informNm', StringUtils::htmlSpecialChars($agreementData['informNm']));
                    break;
                case 'private':
                    //--- 약관, 개인정보 데이터
                    $privateWithManager = $buyerInform->getPrivateWithManager($mallSno);

                    //--- 메일도메인
                    $emailDomain = gd_array_change_key_value(gd_code('01004'));
                    $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);

                    $this->setData('emailDomain', $emailDomain);
                    $this->setData('private', $privateWithManager);
                    $this->setData('informNm', StringUtils::htmlSpecialChars($privateWithManager['informNm']));
                    break;
                case 'privateItem':
                    //--- 약관 표
                    $privateApproval = $buyerInform->getInformData(BuyerInformCode::PRIVATE_APPROVAL, $mallSno);
                    $privateApprovalTableOption = $agreementWithPrivateUtil->getTableByPrivateApprovalOption($mallSno);
                    $privateConsignTable = $agreementWithPrivateUtil->getTableByPrivateConsign($mallSno);
                    $privateOfferTable = $agreementWithPrivateUtil->getTableByPrivateOffer($mallSno);
                    $privateGuestOrder = $buyerInform->getInformData(BuyerInformCode::PRIVATE_GUEST_ORDER, $mallSno);
                    $privateGuestBoardWrite = $buyerInform->getInformData(BuyerInformCode::PRIVATE_GUEST_BOARD_WRITE, $mallSno);
                    $privateGuestCommentWrite = $buyerInform->getInformData(BuyerInformCode::PRIVATE_GUEST_COMMENT_WRITE, $mallSno);
                    $privateProvider = $buyerInform->getInformData(BuyerInformCode::PRIVATE_PROVIDER, $mallSno);
                    $privateMarketing = $buyerInform->getInformData(BuyerInformCode::PRIVATE_MARKETING, $mallSno);

                    $this->setData('privateApprovalContent', $privateApproval['content']);
                    $this->setData('privateApprovalTableOption', $privateApprovalTableOption);
                    $this->setData('privateConsignTable', $privateConsignTable);
                    $this->setData('privateOfferTable', $privateOfferTable);
                    $this->setData('privateGuestOrderContent', $privateGuestOrder['content']);
                    $this->setData('privateGuestBoardWriteContent', $privateGuestBoardWrite['content']);
                    $this->setData('privateGuestCommentWriteContent', $privateGuestCommentWrite['content']);
                    $this->setData('privateProviderContent', $privateProvider['content']);
                    $this->setData('privateMarketing', $privateMarketing);
                    break;
            }
            $this->setData('today', date('Y.m.d'));
            $this->addScript(['member.js']);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
