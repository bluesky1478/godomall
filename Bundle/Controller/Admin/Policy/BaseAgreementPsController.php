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

namespace Bundle\Controller\Admin\Policy;

use Component\Agreement\BuyerInform;
use Component\Policy\BaseAgreementPolicy;
use Request;

/**
 * Class BaseAgreementPsController
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class BaseAgreementPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $mallSno = gd_isset(Request::post()->get('mallSno'), 1);
            $postValue = Request::post()->all();
            $buyerInform = new BuyerInform();
            $baseAgreementPolicy = new BaseAgreementPolicy();
            switch (Request::post()->get('mode')) {
                case 'modifyInform':
                    $buyerInform->modifyInform();
                    $this->layer(__('저장되었습니다.'));
                    break;
                case 'modifyInformTitle':
                    $buyerInform->modifyInformTitle($postValue['informCd'], $postValue['informNm'], $mallSno);
                    $this->json(
                        [
                            'message' => '저장되었습니다.',
                            'title' => $postValue['informNm'],
                            'code'    => 200,
                        ]
                    );
                    break;
                case 'view':
                    $contents = $buyerInform->getInformData(Request::post()->get('informCd'), $mallSno);
                    $this->json(
                        [
                            'title'   => $contents['informNm'],
                            'content' => $contents['content'],
                            'code'    => 200,
                        ]
                    );
                    break;
                default:
                    $buyerInform->saveAgreement(Request::post()->get('agreement'), Request::post()->get('agreementModeFl'), $mallSno);
                    $baseAgreementPolicy->setRequestFiles(Request::files()->all());
                    if (Request::post()->get('agreementModeFl', '') == 'y') {
                        $baseAgreementPolicy->setFairTradeLogo(
                            [
                                'logoFl'            => Request::post()->get('logoFl', 'default'),
                                'logoUploadFile'    => Request::files()->get('logoUploadFile'),
                                'logoUploadFileTmp' => Request::post()->get('logoUploadFileTmp'),
                                'uploadDeleteFl'    => Request::post()->get('uploadDeleteFl', 'n'),
                            ]
                        );
                        $baseAgreementPolicy->saveAgreementDate();
                        $this->layer(__('저장이 완료되었습니다.'));
                    } else {
                        $baseAgreementPolicy->setFairTradeLogo(
                            [
                                'logoFl' => 'no',
                            ]
                        );
                    }
                    $this->json(
                        [
                            'message' => __('저장이 완료되었습니다.'),
                            'code'    => 200,
                        ]
                    );
                    break;
            }
        } catch (\Exception $e) {
            if (Request::isAjax()) {
                $this->json([
                    'error' => [
                        'message' => $e->getMessage(),
                    ]
                ]);
            } else {
                throw $e;
            }
        }
    }
}
