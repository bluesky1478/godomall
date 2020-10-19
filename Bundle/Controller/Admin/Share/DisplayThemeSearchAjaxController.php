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


use Request;
use Globals;


class DisplayThemeSearchAjaxController  extends \Controller\Admin\Controller
{

	/**
	 * 상품 QNA 쓰기
	 *
	 * @author artherot
	 * @version 1.0
	 * @since 1.0
	 * @copyright Copyright (c), Godosoft
	 * @throws Except
	 */
	public function index()
	{
		//--- 각 배열을 trim 처리
		$postValue = Request::post()->toArray();


		if (gd_isset($postValue['mode']) == 'next_select' && gd_isset($postValue['value'])) {

			$goods = \App::load('\\Component\\Goods\\GoodsAdmin');
			$getData = $goods->getGoodsListDisplayThemeMultiData($postValue);
			$i		= 0;
			$tmp	= array();
			foreach ($getData as $key => $val) {
				$tmp[$i]['optionValue']	= $val['themeCode'];
				$tmp[$i]['optionText']	= $val['themeNm'];
				$i++;
			}
			if (!empty($tmp)) {
				echo json_encode($tmp);
			}
		}
		exit;
	}
}

