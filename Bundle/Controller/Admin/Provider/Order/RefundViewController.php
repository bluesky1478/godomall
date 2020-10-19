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
namespace Bundle\Controller\Admin\Provider\Order;

use Component\Member\Manager;
use Exception;

/**
 * 환불 완료 리스트 페이지
 * [관리자 모드] 환불 완료 리스트 페이지
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @param array $get
 * @param array $post
 * @param array $files
 * @throws Exception
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class RefundViewController extends \Controller\Admin\Order\RefundViewController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        // 공급사 정보 설정
        $isProvider = Manager::isProvider();
        $this->setData('isProvider', $isProvider);

        parent::index();
    }
}
