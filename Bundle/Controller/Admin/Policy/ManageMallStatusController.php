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

use Globals;
use Request;

/**
 * 솔루션 이용현황 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class ManageMallStatusController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'management', 'status');

        // --- 페이지 데이터
        try {
            // 데이터 인코드
            $key = '54bde3a626f4ae7cada55fda43e0ff48';
            $setData = [];
            $setData['pageKey'] = Globals::get('gLicense.godosno'); // 쇼핑몰 sno
            $setData['serviceType'] = Globals::get('gLicense.ecCode'); // 쇼핑몰 서비스타입
            $setData['version'] = Globals::get('gLicense.version'); // 쇼핑몰 버전

            // 계정 용량
            $rentalDisk = \App::load('\\Component\\Mall\\RentalDisk');
            $disk = $rentalDisk->diskUsage();
            if (isset($disk['usedDiskMb'])) {
                $setData['disk'] = $disk['usedDiskMb']; // 사용중인 디스크 용량
            }

            // 상품수
            $db = \App::load('DB');
            $setData['goods'] = $db->table_status(DB_GOODS, 'Rows');; // 등록된 상품 수

            $ret = serialize($setData) . $key;
            $data = base64_encode($ret);

            // 호출경로
            $ifrsrc = 'https://www.godo.co.kr/userinterface/_godoConn/godo5_information.php?data=' . $data;

        } catch (\Exception $e) {
            //echo $e->getMessage();
        }

        // --- 관리자 디자인 템플릿
        $this->setData('ifrsrc', gd_isset($ifrsrc));
    }
}
