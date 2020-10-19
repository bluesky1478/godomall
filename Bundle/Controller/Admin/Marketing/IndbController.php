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

namespace Bundle\Controller\Admin\Marketing;

use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Utility\HttpUtils;
use Request;
use Component\Marketing\DaumCpc;

class IndbController extends \Controller\Admin\Controller
{
    public function index()
    {
        $mode = Request::post()->get('mode') ?? Request::get()->get('mode');
        $daumCpc = new DaumCpc;
        switch($mode){
            case "regist":
                // 파라미터 검증
                $msg = $daumCpc->chkRegist(Request::post()->toArray());
//                if($msg)msg($msg,0);
                if($msg) {
                    throw new AlertOnlyException($msg);
                }
                // 로고이미미지 처리
                if(!$daumCpc->registLogoFile()){
                    throw new AlertOnlyException(__("로고 이미지는 필수 입니다."));
                }

                // 파라미터 변수 재가공
                foreach(Request::post()->toArray() as $k => $v){

                    if(is_array($v)) foreach($v as $k1 => $v1){
//                        $data[$k][$k1] =  iconv('utf-8','euc-kr', $v1);
                        $data[$k][$k1] = gd_encode_godo_connect($v1);
                    }else{
//                        $data[$k] =  iconv('utf-8','euc-kr', $v);
                        $data[$k] = gd_encode_godo_connect($v);
                    }
                }

//                debug($data);
//                exit;
                //$logourl = "http://".$_SERVER['HTTP_HOST'].$cfg['rootDir']."/data/";
                $logourl = Request::getDomainUrl().\UserFilePath::data('dburl','daumcpc')->www();

                $data['logo0'] = ($daumCpc->daumCpc['logo0'] ? gd_encode_godo_connect($logourl.$daumCpc->daumCpc['logo0']) : '');
                $data['logo1'] = ($daumCpc->daumCpc['logo1'] ? gd_encode_godo_connect($logourl.$daumCpc->daumCpc['logo1']) : '');
                $data['ip'] = gd_encode_godo_connect(Request::getRemoteAddress());

                // 서비스신청
//                $this->encoding("UTF-8", "EUC-KR", $data);
//                echo 'xxxxxxxxxx';
//                exit;
                $out = HttpUtils::remoteGet("http://gongji.godo.co.kr/userinterface/daum_cpc/cpc_indb.php", $data, $port=80);

                if($out == 1){
                    echo("<script>parent.location.reload();</script>");
                    exit;
                }
                else {

//                    throw new LayerNotReloadException($out);
                    exit;
                }
                break;
            case "daumCpc":
                $daumCpc->daumCpc['useYN'] = Request::post()->get('daumCpc.useYN');
                $daumCpc->daumCpc['nv_pcard'] = Request::post()->get('daumCpc.nv_pcard');
                $daumCpc->daumCpc['goodshead'] = Request::post()->get('daumCpc.goodshead');
                $daumCpc->configration();
//                msg("설정 되었습니다.",0);
                $this->layer(__('설정 되었습니다.'));
                break;
            case "review_init":
                $daumCpc->daumCpc['try'] = '';
                $daumCpc->configration();
                $this->layer(__('상품평 설정 초기화 성공'));
                break;
        }
    }

    public function encoding($s1, $s2, &$arr) { // 인코딩
        foreach(array_keys($arr) as $key){
            $arr[$key] = iconv($s1,$s2, $arr[$key]);
        }
    }
}
