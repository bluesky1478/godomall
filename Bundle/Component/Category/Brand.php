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

namespace Bundle\Component\Category;

use Component\Mall\Mall;
use Session;
use Request;


/**
 * 브랜드 객체가 별도로 필요하여 wrapping 클래스로 생성하고 강제로 brand를 생성자에 넘겨주도록 추가
 *
 * @package Bundle\Component\Category
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class Brand extends \Component\Category\Category
{
    /**
     * 생성자
     *
     * @param string $cateType 카테고리 종류(goods,brand) , null인 경우 상품 카테고리 , (기본 null)
     */
    public function __construct()
    {
        parent::__construct('brand');
    }


    /**
     * 카테고리 정보
     *
     * @param string  $cateCd     카테고리 코드
     * @param integer $depth      출력 depth
     * @param boolean $division   구분자 출력 여부
     * @param boolean $goodsCntFl 상품수 출력 여부
     * @param boolean $userMode   사용자 화면 출력 (기본 false)
     * @param boolean $displayFl  노출여부와 상관없이 보이게 (기본 false)
     *
     * @return string 카테고리 정보
     */
    public function getBrandCodeInfo($cateCd = null,$depth = 4,$cateNm = null, $tree = true, $orderBy = null, $search = false)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        gd_isset($mallBySession['sno'],DEFAULT_MALL_NUMBER);

        $arrWhere = $arrBind = [];
        $arrCateCd = null;

        if (Request::isMobile()) {
            $displayField = "cateDisplayMobileFl";
        } else {
            $displayField = "cateDisplayFl";
        }

        $arrWhere[] = "FIND_IN_SET(".$mallBySession['sno'].",mallDisplay) AND ".$displayField." = 'y'";
        $globalMall = false;
        if($mallBySession['sno'] == '2') { //영문몰 체크
            $arrWhere[] = "g.mallSno = '" . $mallBySession['sno'] . "'";
            $globalMall = true;
        }
        if($cateNm) {
            if($search) {
                if($globalMall === true) {
                    $arrWhere[] = '(g.cateNm LIKE concat(?,\'%\'))';
                    $this->db->bind_param_push($arrBind, 's', strtolower($cateNm));
                }
                else{
                    $arrWhere[] = '(cateNm LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($arrBind, 's', strtolower($cateNm));
                }
            } else {
                if (preg_match("/[\xA1-\xFE\xA1-\xFE]/", $cateNm)) {
                    if ($globalMall === true) {
                        $searchCateNm = 'g.cateNm';
                    }
                    else {
                        $searchCateNm = 'cateNm';
                    }
                    switch ($cateNm)    //TODO:GLOBAL 초성검색
                    {
                        case 'ㄱ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^(ㄱ|ㄲ)' OR ( ".$searchCateNm." >= '가' AND ".$searchCateNm." < '나' )) ";
                            break;
                        case 'ㄴ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㄴ' OR ( ".$searchCateNm." >= '나' AND ".$searchCateNm." < '다' )) ";
                            break;
                        case 'ㄷ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^(ㄷ|ㄸ)' OR ( ".$searchCateNm." >= '다' AND ".$searchCateNm." < '라' )) ";
                            break;
                        case 'ㄹ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㄹ' OR ( ".$searchCateNm." >= '라' AND ".$searchCateNm." < '마' )) ";
                            break;
                        case 'ㅁ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㅁ' OR ( ".$searchCateNm." >= '마' AND ".$searchCateNm." < '바' )) ";
                            break;
                        case 'ㅂ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㅂ' OR ( ".$searchCateNm." >= '바' AND ".$searchCateNm." < '사' )) ";
                            break;
                        case 'ㅅ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^(ㅅ|ㅆ)' OR ( ".$searchCateNm." >= '사' AND ".$searchCateNm." < '아' )) ";
                            break;
                        case 'ㅇ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㅇ' OR ( ".$searchCateNm." >= '아' AND ".$searchCateNm." < '자' )) ";
                            break;
                        case 'ㅈ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^(ㅈ|ㅉ)' OR ( ".$searchCateNm." >= '자' AND ".$searchCateNm." < '차' )) ";
                            break;
                        case 'ㅊ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㅊ' OR ( ".$searchCateNm." >= '차' AND ".$searchCateNm." < '카' )) ";
                            break;
                        case 'ㅋ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㅋ' OR ( ".$searchCateNm." >= '카' AND ".$searchCateNm." < '타' )) ";
                            break;
                        case 'ㅌ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㅌ' OR ( ".$searchCateNm." >= '타' AND ".$searchCateNm." < '파' )) ";
                            break;
                        case 'ㅍ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㅍ' OR ( ".$searchCateNm." >= '파' AND ".$searchCateNm." < '하' )) ";
                            break;
                        case 'ㅎ':
                            $arrWhere[] = "(".$searchCateNm." RLIKE '^ㅎ' OR ( ".$searchCateNm." >= '하')) ";
                            break;
                        default:
                    }

                } else if ($cateNm == 'etc') {
                    if($globalMall === true) {
                        $arrWhere[] = "g.cateNm  < '가' AND g.cateNm NOT REGEXP  '^[a-zA-Z]'";
                    }else {
                        $arrWhere[] = "cateNm  < '가' AND cateNm NOT REGEXP  '^[a-zA-Z]'";
                    }
                } else {
                    if($globalMall === true) {
                        $arrWhere[] = '(g.cateNm LIKE concat(?,\'%\'))';
                    }
                    else {
                        $arrWhere[] = '(cateNm LIKE concat(?,\'%\'))';
                    }
                    $this->db->bind_param_push($arrBind, 's', strtolower($cateNm));
                }
            }

            if (is_null($depth) === false && is_numeric($depth)) {
                $depth = min($depth, 4); //출력Depth가 4차를 넘지 않도록 설정
                if ($globalMall === true) {
                    $arrWhere[] = 'length( g.cateCd ) <= ' . (($depth * $this->cateLength));
                }
                else {
                    $arrWhere[] = 'length( cateCd ) <= ' . (($depth * $this->cateLength));
                }
            }

            //성인인증안된경우 노출체크 상품은 노출함
            if (gd_check_adult() === false) {
                $arrWhere[] = '(cateOnlyAdultFl = \'n\' OR (cateOnlyAdultFl = \'y\' AND cateOnlyAdultDisplayFl = \'y\'))';
            }

            //접근권한 체크
            if (gd_check_login()) {
                $arrWhere[] = '(catePermission !=\'2\'  OR (catePermission=\'2\' AND FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(catePermissionGroup,"'.INT_DIVISION.'",","))) OR (catePermission=\'2\' AND !FIND_IN_SET(\''.Session::get('member.groupSno').'\', REPLACE(catePermissionGroup,"'.INT_DIVISION.'",",")) AND catePermissionDisplayFl =\'y\'))';
            } else {
                $arrWhere[] = '(catePermission IS NULL OR catePermission=\'all\' OR (catePermission !=\'all\' AND catePermissionDisplayFl =\'y\'))';
            }

            $this->db->strWhere = implode(' AND ', $arrWhere);
            if ($globalMall === true) {
                $this->db->strJoin = ' LEFT JOIN ' . DB_CATEGORY_BRAND_GLOBAL . ' AS g ON cate.cateCd = g.cateCd';
            }
            if($orderBy == null && $globalMall !== true) {
               $orderBy = 'cateCd ASC';
            }
            $this->db->strOrder = $orderBy;
            if ($globalMall === true) {
                $cateField = ' g.cateCd,';
            }
            else{
                $cateField = ' cateCd,';
            }

            $getData = $this->getCategoryInfo(null,$cateField.$displayField.' as cateDisplay',$arrBind,true);

            foreach($getData as $k => $v) {
                $arrCateCd[] = $v['cateCd'];
                $cateDepth = (strlen($v['cateCd']) / $this->cateLength);
                for ($i = 1; $i < $cateDepth; $i++) {
                    $arrCateCd[] = substr($v['cateCd'], 0, ($i * $this->cateLength));
                }
            }

            $arrCateCd = array_unique($arrCateCd);

            unset($this->db->strWhere);
        }

        if(($cateNm && $arrCateCd) || $cateNm == null)  {
            $getData =  $this->getCategoryData($arrCateCd, null, 'cateCd, cateNm,cateOverImg,cateImg,cateSort',$arrWhere[0]." AND divisionFl = 'n'", $orderBy);
        }

        if (empty($getData) === false) {
            if($tree) {
                return $this->getTreeArray($getData, false);
            } else {
                return $this->getSortArray($getData);
            }
        } else {
            return false;
        }
    }

    private function getSortArray($data){
        $return = [];
        $english_alphabet= range('A', 'Z') ;
        $kored_alphabet = array('ㄱ','ㄴ','ㄷ','ㄹ','ㅁ','ㅂ','ㅅ','ㅇ','ㅈ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');
        foreach($data as $key => $val) {
            $tmp = $this->check_uniord($val['cateNm']);
            if(in_array($tmp, $kored_alphabet)) {
                $return['korean'][$tmp][$val['cateNm']] = $val;
            } else if(in_array($tmp, $english_alphabet)) {
                $return['english'][$tmp][$val['cateNm']] = $val;
            } else {
                $return['etc'][$tmp][$val['cateNm']] = $val;
            }
        }
        return $return;
    }

    private function uniord($c) {
        $h = ord($c{0});
        if ($h <= 0x7F) {
            return $h;
        } else if ($h < 0xC2) {
            return false;
        } else if ($h <= 0xDF) {
            return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
        } else if ($h <= 0xEF) {
            return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6
                | (ord($c{2}) & 0x3F);
        } else if ($h <= 0xF4) {
            return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12
                | (ord($c{2}) & 0x3F) << 6
                | (ord($c{3}) & 0x3F);
        } else {
            return false;
        }
    }

    private function check_uniord($c) {
        $h = $this->uniord($c);
        if($h>=44032 && $h<=45207) return "ㄱ";
        if($h>=45208 && $h<=45795) return "ㄴ";
        if($h>=45796 && $h<=46971) return "ㄷ";
        if($h>=46972 && $h<=47559) return "ㄹ";
        if($h>=47560 && $h<=48147) return "ㅁ";
        if($h>=48148 && $h<=49323) return "ㅂ";
        if($h>=49324 && $h<=50499) return "ㅅ";
        if($h>=50500 && $h<=51087) return "ㅇ";
        if($h>=51088 && $h<=52263) return "ㅈ";
        if($h>=52264 && $h<=52851) return "ㅊ";
        if($h>=52852 && $h<=53439) return "ㅋ";
        if($h>=53440 && $h<=54027) return "ㅌ";
        if($h>=54028 && $h<=54615) return "ㅍ";
        if($h>=54616 && $h<=55203) return "ㅎ";
        if($h==65 || $h==97) return "A";
        if($h==66 || $h==98) return "B";
        if($h==67 || $h==99) return "C";
        if($h==68 || $h==100) return "D";
        if($h==69 || $h==101) return "E";
        if($h==70 || $h==102) return "F";
        if($h==71 || $h==103) return "G";
        if($h==72 || $h==104) return "H";
        if($h==73 || $h==105) return "I";
        if($h==74 || $h==106) return "J";
        if($h==75 || $h==107) return "K";
        if($h==76 || $h==108) return "L";
        if($h==77 || $h==109) return "M";
        if($h==78 || $h==110) return "N";
        if($h==79 || $h==111) return "O";
        if($h==80 || $h==112) return "P";
        if($h==81 || $h==113) return "Q";
        if($h==82 || $h==114) return "R";
        if($h==83 || $h==115) return "S";
        if($h==84 || $h==116) return "T";
        if($h==85 || $h==117) return "U";
        if($h==86 || $h==118) return "V";
        if($h==87 || $h==119) return "W";
        if($h==88 || $h==120) return "X";
        if($h==89 || $h==121) return "Y";
        if($h==90 || $h==122) return "Z";
        return "ETC";
    }

}
