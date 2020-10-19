<?php

namespace Bundle\Controller\Admin\Promotion;

use App;
use Request;
use Framework\Debug\Exception\LayerException;
use Exception;

class InsgoWidgetPsController extends \Controller\Admin\Controller
{
    public function Index()
    {
        $postValue = Request::post()->toArray();
        try {
            $returnArray = [];
            $widget = App::load('\\Component\\Promotion\\InsgoWidget');
            switch($postValue['mode']) {
                case 'modify': // 수정
                case 'regist': // 저장
                    /* 공통정보 */
                    $returnArray['widgetSno'] = $postValue['widgetSno']; // sno
                    //$returnArray['widgetAccessToken'] = $postValue['widgetAccessToken']; // 엑세스토큰
                    $returnArray['widgetInstagramId'] = $postValue['widgetInstagramId']; // 인스타그램 아이디
                    $returnArray['widgetName'] = $postValue['widgetName']; // 위젯명
                    $returnArray['widgetDisplayType'] = $postValue['widgetDisplayType']; // 위젯타입
                    $returnArray['widgetThumbnailSize'] = $postValue['widgetThumbnailSize']; // 썸네일사이즈
                    if($postValue['widgetThumbnailSize'] == 'hand') {
                        $returnArray['widgetThumbnailSizePx'] = $postValue['widgetThumbnailSizePx']; // 썸네일사이즈(수동 픽셀)
                    }
                    $returnArray['widgetThumbnailBorder'] = $postValue['widgetThumbnailBorder']; // 이미지테두리
                    $returnArray['widgetOverEffect'] = $postValue['widgetOverEffect']; // 마우스오버시효과

                    switch($postValue['widgetDisplayType']) {
                        case 'grid':
                            $returnArray['widgetWidthCount'] = $postValue['widgetWidthCount']; // 레이아웃(가로)
                            $returnArray['widgetHeightCount'] = $postValue['widgetHeightCount']; // 레이아웃(세로)
                            $returnArray['widgetBackgroundColor'] = $postValue['widgetBackgroundColor']; // 위젯배경색
                            $returnArray['widgetImageMargin'] = $postValue['widgetImageMargin']; // 이미지간격
                            break;
                        case 'scroll':
                            $returnArray['widgetWidth'] = $postValue['widgetWidth']; // 위젯가로사이즈
                            $returnArray['widgetAutoScroll'] = $postValue['widgetAutoScroll']; // 자동스크롤
                            $returnArray['widgetScrollSpeed'] = $postValue['widgetScrollSpeed']; // 전환속도선택
                            $returnArray['widgetSideButtonColor'] = $postValue['widgetSideButtonColor']; // 좌우전환버튼색상
                            break;
                        case 'slide':
                            $returnArray['widgetBackgroundColor'] = $postValue['widgetBackgroundColor']; // 위젯배경색
                            $returnArray['widgetScrollSpeed'] = $postValue['widgetScrollSpeed']; // 전환속도선택
                            $returnArray['widgetScrollTime'] = $postValue['widgetScrollTime']; // 전환시간선택
                            $returnArray['widgetEffect'] = $postValue['widgetEffect']; // 효과선택
                            break;
                    }

                    $iframeUri = $widget->setUriHash($returnArray);
                    if(!$iframeUri) {
                        throw new LayerException('fail to get iframe uri.');
                    }

                    $iframeSize = $widget->getIframeSize($returnArray);
                    $iframeHtml = $widget->getIframeHtml($iframeSize, $iframeUri);
                    if(!$iframeHtml) {
                        throw new LayerException('fail to get iframe html');
                    }

                    $widget->setWidgetData($returnArray, $iframeUri);
                    if($returnArray['widgetSno']){  //수정 시 인스고 캐시 삭제
                        $widget->removeCacheFile($returnArray['widgetSno']);
                    }
                    $this->layer(__('저장되었습니다.'), 'parent.location.replace("../promotion/insgo_widget_list.php");');
                    break;
                case 'preview': // 미리보기
                    break;
                case 'delete': // 삭제
                    $widget->deleteData($postValue['sno']);
                    $this->layer(__('삭제가 완료되었습니다.'), 'top.location.replace("../promotion/insgo_widget_list.php");');
                    break;
                default:
                    throw new Exception(__('인스고 위젯 정보가 유효하지 않습니다.'));
                    break;
            }

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }

}
