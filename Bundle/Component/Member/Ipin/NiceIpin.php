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

namespace Bundle\Component\Member\Ipin;


use Logger;
use Request;

class NiceIpin
{
    /**
     * @var string
     * 모듈 경로설정, '/절대경로/모듈명' 으로 정의
     */
    private $modulePath;
    /**
     * @var
     * 암호화된 결과 데이타를 리턴받으실 URL, 예 - http://www.test.co.kr/ipin_process.php
     */
    private $returnURL;
    /**
     * @var
     * IPIN 서비스 사이트 코드        (NICE신용평가정보에서 발급한 사이트코드)
     */
    private $siteCode;
    /**
     * @var
     * IPIN 서비스 사이트 패스워드    (NICE신용평가정보에서 발급한 사이트패스워드)
     */
    private $sitePassword;

    function __construct()
    {
        // 서버 OS 버전 체크
        if (PHP_INT_SIZE == 4) $tmpServerOS = 32;
        if (PHP_INT_SIZE == 8) $tmpServerOS = 64;

        $this->modulePath = SYSPATH_IPIN_MODULE . 'NICE_iPINClient' . $tmpServerOS;
        $this->returnURL = Request::getDomainUrl() . DS . 'member/ipin/nice_ipin_process.php';

        $ipinConfig = gd_policy('member.ipin');
        $this->siteCode = $ipinConfig['siteCode'];
        $this->sitePassword = $ipinConfig['sitePass'];
    }

    /**
     * sequence
     * 일련번호 생성
     *
     * @return string
     */
    public function sequence()
    {
        $CPRequest = exec($this->modulePath . ' SEQ ' . $this->siteCode);
        Logger::info(__METHOD__ . ' $CPRequest=>' . $CPRequest . ' ' . $this->modulePath . ' SEQ ' . $this->siteCode);
        return $CPRequest;
    }

    /**
     * encrypt
     * 암호화
     *
     * @param string $type
     * @param $CPRequest
     * @return string
     */
    public function encrypt($type = 'REQ', $CPRequest)
    {
        $encData = exec($this->modulePath . ' ' . $type . ' ' . $this->siteCode . ' ' . $this->sitePassword . ' ' . $CPRequest . ' ' . $this->returnURL);
        return $encData;
    }
}
