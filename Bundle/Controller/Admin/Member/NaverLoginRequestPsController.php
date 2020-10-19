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

namespace Bundle\Controller\Admin\Member;

use Bundle\Component\Policy\NaverLoginPolicy;
use Component\Godo\GodoNaverServerApi;
use Framework\Debug\Exception\Except;
use League\Flysystem\Exception;
use Request;
use FileHandler;
use UserFilePath;
use Framework\Debug\Exception\AlertRedirectException;

/**
 * Class NaverLoginRequestPsController
 * @package Bundle\Controller\Admin\Member
 */
class NaverLoginRequestPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        \Logger::debug(__METHOD__, \Request::post()->all());
        try {
            $godoApi = new GodoNaverServerApi();
            $value = \Request::post()->all() ? \Request::post()->all() : \Request::get()->all();
            $redirectURL = '../member/naver_login_config.php';

            $imgFl = false;
            if ($value['parentForm'] == 'form') {
                $parentForm = 'form';
                $parentMode = 'update';
                $parentMessage = '수정';
                foreach (['png', 'jpg', 'gif'] as $ext) {
                    if (FileHandler::isExists(UserFilePath::data('common', 'naver_login_icon.' . $ext)) === true) {
                        $imgFl = true;
                        break;
                    }
                }
            } else {
                $parentForm = 'layerForm';
                $parentMode = 'regist';
                if($value['firstCheck'] == 'f') {
                    $parentMessage = '신청';
                } else {
                    $parentMessage = '재신청';
                }
            }

            switch ($value['mode']) {
                case 'regist':
                case 'update':
                    $registResult = $godoApi->createClient($value, \Request::files()->get('serviceImage'));
                    $redirectTarget = 'parent';
                    \Logger::info(__METHOD__ . ' - ' . $value['mode'] . ' : ' . $registResult);

                    // 오류 발생
                    if($registResult['error']) {
                        throw new AlertRedirectException(__($registResult['desc']), 200, null, $redirectURL, $redirectTarget);
                    }

                    if($registResult) {
                        throw new AlertRedirectException(__('네이버 아이디 로그인 ' . $parentMessage . '이 완료되었습니다.'), 200, null, $redirectURL, $redirectTarget);
                    } else {
                        throw new AlertRedirectException(__('처리중에 오류가 발생하여 실패 하였습니다.'), 200, null, $redirectURL, $redirectTarget);
                    }
                    break;
                case 'adminAuthorize':
                    if(in_array($value['serviceURL'], ['http://', 'https://'])) {
                        $script = 'opener.' . $parentForm . '.confirmyn.value = "n";';
                        $script .= 'alert("' . __('대표 URL을 정확하게 입력해주세요.') . '");';
                        $script .= 'self.close();';
                        $this->js($script);
                    } else if (\Request::files()->get('serviceImage.size') > 0) {
                        $serviceImage = \Request::files()->get('serviceImage');
                        $tmpExt = FileHandler::getFileInfo($serviceImage['name'])->getExtension();
                        if ($serviceImage['size'] > 500 * 1024) {
                            $script = 'opener.' . $parentForm . '.confirmyn.value = "n";';
                            $script .= 'alert("' . __('500kb이하의 로고 이미지만 등록이 가능합니다.') . '");';
                            $script .= 'self.close();';
                            $this->js($script);
                        } else if (in_array(strtolower($tmpExt), ['jpg', 'png', 'gif']) === false) {
                            $script = 'opener.' . $parentForm . '.confirmyn.value = "n";';
                            $script .= 'alert("' . __('로고 이미지는 jpg, png, gif 형식의 파일만 사용하실 수 있습니다.') . '");';
                            $script .= 'self.close();';
                            $this->js($script);
                        }
                    } else if($parentForm == 'layerForm' && $value['firstCheck'] == 'f' && \Request::files()->get('serviceImage.size') <= 0) {
                        $script = 'opener.' . $parentForm . '.confirmyn.value = "n";';
                        $script .= 'alert("' . __('로고이미지를 업로드해주세요.') . ' code:-1' . '");';
                        $script .= 'self.close();';
                        $this->js($script);
                    } else if(($parentForm == 'form' && $value['imageURL'] == '') || $imgFl === false) {
                        $script = 'opener.' . $parentForm . '.confirmyn.value = "n";';
                        $script .= 'alert("' . __('로고이미지를 업로드해주세요.') . ' code:-2' . '");';
                        $script .= 'self.close();';
                        $this->js($script);
                    }

                    $this->redirect($godoApi->adminAuthorize($value['parentForm']));
                    break;
                case 'setToken':
                    try {
                        if (empty($value['token']) === true) {
                            \Logger::info(__METHOD__ . ' - ' . $value['mode'] . ' : 토큰값이 유효하지 않습니다.');
                            throw new Exception(__('토큰값이 유효하지 않습니다.'));
                        }

                        $script = 'opener.' . $parentForm . '.token.value = "' . str_replace(' ', '+', $value['token']) . '";';
                        $script .= 'opener.' . $parentForm . '.mode.value = "' . $parentMode . '";';
                        $script .= 'opener.' . $parentForm . '.confirmyn.value = "y";';
                        $script .= 'opener.document.' . $parentForm . '.target = "ifrmProcess";';
                        $script .= 'opener.document.' . $parentForm . '.submit();';
                        $script .= 'self.close();';
                        $this->js($script);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }
                    break;
                case 'useChange':
                    if($value['imageURL'] == '' || $imgFl === false) {
                        $script  = 'alert("' . __('로고이미지를 업로드해주세요.') . ' code:-3' . '");';
                        $script .= 'location.replace("../member/naver_login_config.php"); ';
                        $this->js($script);
                    }
                    $changeResult = $godoApi->changeUse($value);
                    if($changeResult) {
                        throw new AlertRedirectException(__('네이버 아이디 로그인 ' . $parentMessage . '이 완료되었습니다.'), 200, null, $redirectURL);
                    } else {
                        throw new AlertRedirectException(__('처리중에 오류가 발생하여 실패 하였습니다.'), 200, null, $redirectURL);
                    }
                    break;
            }
        } catch (Exception $e) {
            \Logger::info(__METHOD__ . ' - ' . $value['mode'] . ' : 네이버 아이디 로그인 사용 설정 저장 중 오류가 발생하였습니다.');
            throw new LayerNotReloadException(__('네이버 아이디 로그인 사용 설정 저장 중 오류가 발생하였습니다.'), $e->getCode(), $e);
        }
    }
}
