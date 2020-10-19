<?php
/**
 * 이벤트 Class
 *
 * @author sj
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Event;

use Component\Database\DBTableField;
use Component\Validator\Validator;
use Framework\Debug\Exception\Except;
use Framework\Utility\ArrayUtils;
use Globals;

class Event
{
	const ERROR_VIEW				= 'ERROR_VIEW';

	const TEXT_INVALID_ARG			= '%s인자가 잘못되었습니다.';
	const TEXT_INVALID_DATE			= '마감된 이벤트입니다. ';

	private $db;
	private $fieldTypes; // db field type

	/**
	 * 생성자
	 */
	public function __construct()
	{
		if (!is_object($this->db)) {
			$this->db = \App::load('DB');
		}
		$this->fieldTypes = DBTableField::getFieldTypes('tableEvent');
	}

	public function getCategoryList($arrCateCd, $cateMode)
	{
		switch($cateMode) {
			case 'category': {
				$cateTable = DB_CATEGORY_GOODS;
				break;
			}
			case 'brand': {
				$cateTable = DB_CATEGORY_BRAND;
				break;
			}
		}

		if (ArrayUtils::isEmpty($arrCateCd) === false) {
			foreach($arrCateCd as $val) {
				$arrWhere[] = "?";
				$this->db->bind_param_push($arrBind, 's', $val);
			}
		}
		else return null;

		$this->db->strField		= "cateCd, cateNm";
		if (ArrayUtils::isEmpty($arrWhere) === false) {
			$this->db->strWhere		= 'cateCd in (' . implode(',', $arrWhere) . ')';
		}

		$query	= $this->db->query_complete();
		$strSQL = 'SELECT '.array_shift($query).' FROM '.$cateTable.' '.implode(' ',$query);
		$data	= gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL,$arrBind));

		if (ArrayUtils::isEmpty($arrCateCd) === false) {
			foreach($arrCateCd as $key => $val) {
				if (ArrayUtils::isEmpty($data) === false) {
					foreach($data as $key2 => $val2) {
						if ($val2['cateCd'] == $val) {
							$getData[] = $val2;
							array_splice($data, $key2, 1);
							break;
						}
					}
				}
			}
		}

		if (ArrayUtils::isEmpty($getData) === false) {
			array_unshift($getData, array('cateCd'=>'', 'cateNm'=>__('전체')));
		}

		return $getData;
	}

	/**
	 * 상품 정보 출력 (상품 리스트)
	 *
	 * @param string $goodsNo 상품번호
	 * @param string $cateCd 카테고리 코드
	 * @param string $brandCd 브랜드 코드
	 * @param string $displayOrder 상품 기본 정렬 Category::getSort()
	 * @param string $imageType 이미지 타입 - 기본 'main'
	 * @param boolean $optionFl 옵션 출력 여부 - true or false (기본 false)
	 * @param boolean $soldOutFl 품절상품 출력 여부 - true or false (기본 true)
	 * @param boolean $brandFl 브랜드 출력 여부 - true or false (기본 true)
	 * @return array 상품 정보
	 */
	public function getGoodsList($goodsNo, $cateCd, $brandCd, $displayOrder, $imageType='main', $optionFl=false, $soldOutFl=true, $brandFl=false)
	{
		if(!$goodsNo) return null;


		$goods = \App::load('\\Component\\Goods\\Goods');
		$goodsList = $goods->goodsDataDisplay('event', $goodsNo.MARK_DIVISION.$cateCd.MARK_DIVISION.$brandCd, null, $displayOrder, $imageType, $optionFl, $soldOutFl, $brandFl);

		if (!$displayOrder) {
			$arrGoods = explode(INT_DIVISION, $goodsNo);
			if (ArrayUtils::isEmpty($arrGoods) === false) {
				foreach($arrGoods as $key => $val) {
					if (ArrayUtils::isEmpty($goodsList) === false) {
						foreach($goodsList as $key2 => $val2) {
							if ($val2['goodsNo'] == $val) {
								$nGoodsList[] = $val2;
								array_splice($goodsList, $key2, 1);
								break;
							}
						}
					}
				}
				if (isset($nGoodsList)) $goodsList = $nGoodsList;
				unset($nGoodsList);
			}
		}

		return $goodsList;
	}

	/**
	 * 이벤트정보
	 * @param string $sno 일련번호
	 * @return array 데이터
	 */
	public function getEventData($sno)
	{
		if (Validator::number($sno, null, null, true) === false) {
			throw new Except(self::ERROR_VIEW,sprintf(__('%s인자가 잘못되었습니다.'), '일련번호'));
		}

		$this->db->strField		= "*";
		$this->db->strWhere		= "sno=?";
		$this->db->bind_param_push($arrBind, 'i', $sno);

		$query	= $this->db->query_complete();
		$strSQL = 'SELECT '.array_shift($query).' FROM '.DB_EVENT.' '.implode(' ',$query);
		$data	= gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL,$arrBind,false));

		if (ArrayUtils::isEmpty($data) === true) {
			throw new Except(self::ERROR_VIEW,sprintf(__('%s인자가 잘못되었습니다.'), '일련번호'));
		}

		$arrStartDt = explode('-', $data['startDt']);
		$arrEndDt = explode('-', $data['endDt']);

		$today = getdate();
		$startDt = mktime(0, 0, 0, $arrStartDt[1], $arrStartDt[2], $arrStartDt[0]);
		$endDt = mktime(23, 59, 59, $arrEndDt[1], $arrEndDt[2], $arrEndDt[0]);
		if ($today[0] < $startDt || $today[0] > $endDt) {
			throw new Except(self::ERROR_VIEW, __('마감된 이벤트입니다.'));
		}
		return $data;
	}
}
