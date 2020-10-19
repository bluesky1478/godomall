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

namespace Bundle\Controller\Mobile\Member\Ipin;

use Core\Base\PageNameResolver\ControllerPageNameResolver;

/**
 * Class NiceIpinProcessController
 * NICE신용평가정보 아이핀 모듈 사용자 인증 정보 처리 페이지
 * 원본 파일명 ipin_process.php
 * NICE신용평가정보 아이핀 버전 : VNO-IPIN Service Version 2.0.P(20080929)
 * - 수신받은 데이터(인증결과)를 메인화면으로 되돌려주고, close를 하는 역활을 합니다.
 * @package Controller\Mobile\Member\Ipin
 * @author  yjwee
 */
class IpinProcessController extends \Controller\Front\Member\Ipin\IpinProcessController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        parent::index();
        $this->setPageName(new ControllerPageNameResolver());
    }
}
