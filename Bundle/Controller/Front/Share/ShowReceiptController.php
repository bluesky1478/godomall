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
namespace Bundle\Controller\Front\Share;

use Component\Payment\CashReceipt;
use Request;

/**
 * 영수증 출력
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class ShowReceiptController extends \Controller\Front\Controller
{

	/**
	 * index
	 *
	 */
	public function index()
	{
		try {
			// --- 모듈 호출
			$cashReceipt = new CashReceipt();

			// _POST 정보
			$postValue = Request::post()->toArray();

			// 영수증 출력 데이타
			$data = $cashReceipt->viewPgReceipt($postValue['orderNo'], $postValue['mode']);
			echo $data;
		}
		catch (\Exception $e) {
			$result['error'] = __('데이타 오류로 영수증을 출력할 수 없습니다.');
			echo json_encode($result);
		}
		exit();
	}
}
