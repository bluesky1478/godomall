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

namespace Bundle\Controller\Admin\Base;

use Globals;
use Request;
use UserFilePath;

/**
 * 계정 용량 확인
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DiskSpaceController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // 계정 용량
        /** @var \Bundle\Component\Mall\RentalDisk $rentalDisk 계정용량 정보 */
        $rentalDisk = \App::load('\\Component\\Mall\\RentalDisk');

        // 재실행 요청시 용량 파일 삭제
        if (Request::post()->has('mode')) {
            $rentalDisk->deleteDuFile(Request::post()->get('mode'));
        }

        // 용량 체크
        $disk = $rentalDisk->diskUsage(Request::post()->get('mode'));

        $setData = [];
        $setData['usedDisk'] = $disk['usedDisk'];
        $setData['supplyDisk'] = $disk['supplyDisk'];
        $setData['usedPer'] = $disk['usedPer'];
        $setData['fullDate'] = $disk['fullDate'];
        $setData['fullLimitDate'] = $disk['fullLimitDate'];

        if ($disk['limitCheck'] === true) {
            $setData['diskTitle'] = __('용량');
        } else {
            $setData['diskTitle'] = __('사용용량');
            $setData['supplyDisk'] = '';
        }

        $this->json($setData);
        exit();
    }
}
