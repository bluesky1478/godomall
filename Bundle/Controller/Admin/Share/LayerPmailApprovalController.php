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
namespace Bundle\Controller\Admin\Share;

use Component\Mail\Pmail;
use Exception;

/**
 * 컴백쿠폰 SMS발송내용 정보보기/수정 레이어 페이지
 *
 */
class LayerPmailApprovalController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // 리퀘스트
            $pMailService = \App::load(Pmail::class);
            $configPmail = $pMailService->getMailConfigPmail();

            $this->setData('configPmail', $configPmail);

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
