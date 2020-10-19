<?php
/**
 * Created by PhpStorm.
 * User: godo
 * Date: 2018-01-17
 * Time: 오후 2:42
 */
?>
<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?>
        <small></small>
    </h3>
    <?php if($useFbCheckValue == 'oldVersion') {?>
    <input type="submit" value="저장" class="btn btn-red old-version-submit">
    <?php } ?>
    <?php if($useFbCheckValue == 'newVersionModify') {?>
        <input type="submit" value="저장" class="btn btn-red new-version-submit">
    <?php } ?>
</div>

<?php if($useFbCheckValue == 'oldVersion') {?>
<div class="design-notice-box mgb10">
    기존 페이스북 광고 설정 기능이 <strong class="text-darkred">Facebook Business Extension(FBE)</strong> 으로 업그레이드 되었습니다.<br>
    <strong class="text-darkred">[Facebook Business Extension 시작하기]를 통해 쇼핑몰과 연동완료 시 기존에 설정했던 내용은 유지되지 않습니다.</strong><br>
    FBE로 연동할 경우 페이스북 페이지 연결, 픽셀 ID 설정, 제품 피드 연동까지 자동으로 처리되므로, 업그레이드하시는 것을 권장드립니다.
</div>
<?php } ?>

<form id="facebookConfigExtension" action="dburl_ps.php" method="post" target= "ifrmProcess">
    <input type="hidden" name="type" value="setFbSettings"/>
    <input type="hidden" name="company" value="facebookExtension"/>
    <input type="hidden" name="mode" value="config"/>
    <input type="hidden" name="domainCodeSaveFl" value="y"/>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <div class="table-title">
            페이스북 광고 설정
        </div>
        <tr>
            <th>Facebook Business Extension 설정</th>
            <td>
                <input type="button" class="btn btn-gray btn-sm" id="fbPopup" value="Facebook Business Extension 시작하기"/>
                <div class="notice-info">
                    Facebook Business Extension(FBE) 을 이용하면 페이스북 페이지 연결, 픽셀 ID 설정, 제품 피드 연동까지 설정 가능합니다
                </div>
                <div class="notice-info">페이스북 '피드 생성' 대상 상품이 많을 경우 피드 파일 생성 시간이 지연될 수 있습니다</div>
                <div class="notice-info">
                    페이스북 광고 설정이 ‘피드 생성’으로 설정된 상품이 1개 이상 있을 경우에만 기능 사용이 가능합니다. (설정은 각각의 상품등록 페이지 > 페이스북 광고 설정에서 설정하실 수 있습니다.)
                </div>
                <div class="notice-danger">
                    크롬 브라우져에서만 정상적으로 동작합니다
                </div>
            </td>
        </tr>
        <tr id="useExtension">
            <th>Facebook Business Extension 설정정보</th>
            <td>
                픽셀 ID: <span id="pixelIdCheck"><?= $fbeData['pixel_id'] ?></span>
                <div>
                    <a href="https://www.facebook.com/products/catalogs/<?= $fbeData['product_catalog_id']?>/products" target="_blank" style="cursor:pointer; color:blue;text-decoration: underline; ">카탈로그 바로가기</a>
                    (마지막 페이스북 제품 피드 생성 정보 : 총 <?= $totalGoodsFeedCnt ?>개, <?= $makeFileTime ?>)
                </div>
                <div class="notice-info">생성된 피드 파일의 업데이트 일정은 페이스북 비즈니스 관리자의 카달로그 설정에서 확인하실 수 있습니다</div>
            </td>
        </tr>
        <?php if($useFbCheckValue == 'newVersionModify') {?>
        <tr>
            <th>도메인 인증코드 설정</th>
            <td>
                <input type="text" name="domainAuthCode" id="domainAuthCode" class="form-control width-2xl" maxlength="100" value="<?php echo gd_isset($fbeData['domainAuthCode'], $data['domainAuthCode']) ?>"/>
                <div class="notice-info">
                    반드시 도메인 인증코드를 설정해주셔야, 정상적으로 페이스북 광고를 진행하실 수 있습니다.자세한 방법은 아래 안내를 참고하여 주시기 바랍니다.
                </div>
            </td>
        </tr>
        <?php } ?>
    </table>
</form>
<?php if($useFbCheckValue == 'oldVersion') {?>
<form id="facebookConfig" action="dburl_ps.php" method="post" target= "ifrmProcess">
    <input type="hidden" name="type" value="config"/>
    <input type="hidden" name="company" value="facebook"/>
    <input type="hidden" name="mode" value="config"/>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <div class="table-title">
        페이스북 광고
        </div>
        <tr>
            <th>페이스북 광고 사용 설정</th>
            <td>
                <label class="radio-inline" >
                    <input type="radio" name="fbUseFl" value="y" <?php echo gd_isset($checked['fbUseFl']['y']); ?> />사용
                </label>
                <label class="radio-inline" >
                    <input type="radio" name="fbUseFl" value="n" <?php echo gd_isset($checked['fbUseFl']['n']); ?> />사용안함
                </label>
                <div class="notice-info">
                    서비스를 사용하시려면, 페이스북 비즈니스 관리자에서 계정을 만들어야 합니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>픽셀 ID</th>
            <td>
                <input type="text" name="fixelId" id="fixelId" class="form-control width-2xl" maxlength="100" value="<?php echo gd_isset($data['fixelId']) ?>"/>
                <div class="notice-info">
                    픽셀 ID는 필수로 입력하셔야 하며, 픽셀 ID는 페이스북 비즈니스 설정의 픽셀 메뉴에서 생성 및 확인하실 수 있습니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>도메인 인증코드 설정</th>
            <td>
                <input type="text" name="domainAuthCode" id="domainAuthCode" class="form-control width-2xl" maxlength="100" value="<?php echo gd_isset($data['domainAuthCode']) ?>"/>
                <div class="notice-info">
                    반드시 도메인 인증코드를 설정해주셔야, 정상적으로 페이스북 광고를 진행하실 수 있습니다.자세한 방법은 아래 안내를 참고하여 주시기 바랍니다.
                </div>
            </td>
        </tr>
    </table>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <div class="table-title">
            페이스북 픽셀 코드 설정
        </div>
        <tr>
            <th>픽셀 코드 설정</th>
            <td>
                <label class="checkbox-inline">
                    <input type="checkbox" name="" value="y" checked="checked" disabled="disabled" /> 기본 코드
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="goodsViewScriptFl" value="y" <?php echo gd_isset($checked['goodsViewScriptFl']['y']); ?> /> 컨텐츠 조회(상품조회)
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="cartScriptFl" value="y" <?php echo gd_isset($checked['cartScriptFl']['y']); ?>/> 장바구니에 담기
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="orderEndScriptFl" value="y" <?php echo gd_isset($checked['orderEndScriptFl']['y']); ?>/> 구매
                </label>
                    <div class="notice-info">
                        페이스북 픽셀을 사용하려면 쇼핑몰에 픽셀 코드가 필요하며, 설정 시 해당 코드가 쇼핑몰에 반영됩니다.
                    </div>
            </td>
        </tr>
    </table>
</form>

<form id="facebookConfig" action="dburl_ps.php" method="post" target= "ifrmProcess">
    <input type="hidden" name="type" value="download"/>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <div class="table-title">
        페이스북 제품 피드 다운로드
    </div>
    <tr>
        <th>제품 피드 다운로드</th>
        <td>
            <input type="submit" class="btn btn-gray btn-sm" value="다운로드"/>
            <div class="notice-info">
                다운로드 받은 파일은 페이스북 비즈니스 설정의 제품 카달로그 메뉴에서 제품 피드로 업로드하실 수 있습니다.
            </div>
            <div class="notice-info">
                상품 > 상품 등록에서 페이스북 제품 피드 설정을 ‘피드 생성’으로 설정한 상품만 제품 피드로 생성됩니다.
            </div>
        </td>
    </tr>
</table>
</form>
<?php } ?>
<form id="fbeIframe" action="facebook_ads_ps.php" method="post" target="ifrmProcess">
<input type="hidden" name="type" value="checkTsvFile"/>
</form>
<script type="text/javascript">
    <!--
    var page = '';
    var popupUrlOri = 'https://www.facebook.com/';
    var path = '/ads/dia';
    var settingsParam;
    var useFbCheckValue = "<?= $useFbCheckValue ?>";
    var merchantId = <?= gd_isset($fbeData['merchant_settings_id'],0) ?>;
    var version = "<?=$useFbCheckValue?>";
    var url = window.location.protocol + '//' + window.location.host;
    var settings =  {'fbUseFl' : 'y'};
    var resetSettings = {'fbUseFl' : 'n'};
        $(document).ready(function () {
            if(useFbCheckValue == 'newVersionModify') {
                $('#fbPopup').val('Facebook Business Extension 변경하기');
                $('#useExtension').show();
            } else {
                $('#useExtension').hide();
            }
            $(document).on('click','.old-version-submit', function (e) {
                $("#facebookConfig").submit();
            });
            $(document).on('click','.new-version-submit', function (e) {
                $("#facebookConfigExtension").submit();
            });
            //facebook business extension 시작하기 버튼 클릭시 동작
            $(document).on('click','#fbPopup', function (e) {
                    if (version == "oldVersion") {
                        chkDialogProgress();
                    } else {
                        fbeActivate();
                    }
            });
            $(document).on('click','input:checkbox[name="goodsViewScriptFl"]', function (e) {
                if($(this).prop("checked")) {
                    $(this).val('y');
                } else {
                    $(this).val('n');
                }
            });
            $(document).on('click','input:checkbox[name="cartScriptFl"]', function (e) {
                if($(this).prop("checked")) {
                    $(this).val('y');
                } else {
                    $(this).val('n');
                }
            });
            $(document).on('click','input:checkbox[name="orderEndScriptFl"]', function (e) {
                if($(this).prop("checked")) {
                    $(this).val('y');
                } else {
                    $(this).val('n');
                }
            });

            if ($(':radio[name="fbUseFl"]:checked').val()=='n') {
                $('#fixelId').prop("disabled",true);
                $('#domainAuthCode').prop("disabled",true);
            }

            $(':radio[name="fbUseFl"]').change(function () {
                if ($(this).val() == 'y') {
                    $('#fixelId').prop("disabled",false);
                    $('#domainAuthCode').prop("disabled",false);
                } else {
                    $('#fixelId').prop("disabled",true);
                    $('#domainAuthCode').prop("disabled",true);
                }
            });
            window.addEventListener('message', receiveMessage, false);
        });

    //기존 설정 존재하면서 초기 fbe 설정시 진입
    function chkDialogProgress() {
        dialog_confirm('페이스북 광고 설정 기능이 Facebook Business Extension(FBE) 으로 업그레이드 되었습니다. 설정 시 기존의 설정값들은 FBE에서 설정된 값으로 업데이트 됩니다. 계속하시겠습니까?', function (result) {
            if (result) {
                fbeActivate();
            } else {
                return false;
            }

        }, '확인', {"cancelLabel": '취소', "confirmLabel": '진행'});
    }

    function fbeActivate() {
        $.post('../marketing/facebook_ads_ps.php', {'type': 'chkGoodsFeedCnt'}, function (data) {
            if(data == 'emptyFeedGoods'){
                alert('페이스북 광고 설정이 “피드 생성”으로 설정된 상품이 1개 이상 있을 경우에만 기능 사용이 가능합니다. (설정은 각각의 상품등록 페이지 > 페이스북 광고 설정에서 설정하실 수 있습니다.)');
            } else {
                var complied = _.template($('#progressFbe').html());
                $("#content-wrap").append(complied());
                $(".js-progress-fbe").show();
                $("#fbeIframe").submit();
            }
        });
    }

    // 페이스북 연동 팝업창 open
    function settingsParamData(data) {
        settingsParam = JSON.parse(data);
        console.log(settingsParam);
        page = window.open (
            popupUrlOri + '/login.php?display=popup&next=' + encodeURIComponent(popupUrlOri + path + '?origin=' + url + ' &merchant_settings_id=' + merchantId), 'DiaWizard',
            ['toolbar=no', 'location=no', 'directories=no', 'status=no', 'menubar=no', 'scrollbars=no', 'resizable=no', 'copyhistory=no', 'width=' + 1800, 'height=' + 800].join(',')
        );
    }
    function sendToFacebook(type, params){
        console.log(type);
        page.postMessage({
            type:type,
            params:params
        }, popupUrlOri+path+'?'+encodeURIComponent('origin='+url+'&merchant_settings_id=' + merchantId));
    }
    function receiveMessage(e){
        console.log(e.data);
        switch(e.data.type){
            case 'reset':
                setReset(e.data);
                break;
            case 'get dia settings':
                sendToFacebook('dia settings', settingsParam);
                break;
            case 'set catalog': //전역으로 저장해두었다가 한꺼번에 저장하기
                setCatalog(e.data);
                break;
            case 'set merchant settings':
                setMerchantSettings(e.data);
                break;
            case 'set pixel':
                setPixel(e.data);
                break;
            case 'gen feed': //팝업창 세팅 완료 이후, 제품 목록에서 지금가져오기 버튼 클릭시 이벤트 발생
                genFeed();
                break;
        }
    }
        function setCatalog(message){
            if(!message.params.catalog_id){
                console.log('Facebook Extension Error: got no catalog_id', message.params);
                sendToFacebook('fail set catalog', message.params);
                return;
            }
            settings.product_catalog_id = message.params.catalog_id;
            saveSetting(settings);
        }
        function setPixel(message){
            if (!message.params.pixel_id) {
                console.error('Facebook Ads Extension Error: got no pixel_id', message.params);
                sendToFacebook('fail set pixel', message.params);
                return;
            }
            settings.pixel_id = message.params.pixel_id;
            if (message.params.pixel_use_pii !== undefined) {
                settings.pixel_use_pii = message.params.pixel_use_pii;
            }
            saveSetting(settings);
        }
        function setMerchantSettings(message){
            if (!message.params.setting_id) {
                console.error('Facebook Extension Error: got no setting_id', message.params);
                sendToFacebook('fail set merchant settings', message.params);
                return;
            }
            settings.merchant_settings_id = message.params.setting_id; //전역변수에 저장하고
            saveSetting(settings);
        }
        function genFeed(){
            sendToFacebook("ack feed");
        }
        function setAccessTokenAndPageId() {

        }

        function setReset(message){
            $.ajax({
                method: "POST",
                url: "dburl_ps.php",
                data: {
                    'type': 'setFbSettings',
                    'company':'facebookExtension',
                    'mode':'config',
                    'value' : resetSettings
                },
                dataType: 'text'
            }).success(function (data) {
                sendToFacebook('ack reset');
                merchantId=0;
            }).error(function (e) {
                alert(e.responseText);
                sendToFacebook('fail reset');
            });
        }

    function progressFbe(size){
        if ($.isNumeric(size) == false) {
            size = "100";
        }
        $("#progressView").text(size + "%");
        $("#progressViewBg").css({
            "background-color": "#fa2828",
            "width": size + "%"
        });
    }
    function progressFbeHide() {
        $(".js-progress-fbe").hide();
        $(".js-progress-fbe").remove();
    }
    function saveSetting(settings){
            if(!settings){
                console.log('Fail response on save_settings_and_sync');
                sendToFacebook('fail save_settings');
                return;
            } else {
                if(merchantId != 0){ // 세팅 이후, 픽셀값 변경시 settings 재설정.
                    settings.merchant_settings_id = merchantId;
                    settings.product_catalog_id = <?= gd_isset($fbeData['product_catalog_id'],0) ?>;
                } else { // 세팅된 값이없는데 세팅값 중 하나라도 공백인 경우 세팅값 다 채우러 리턴.
                    if (_.isUndefined(settings.merchant_settings_id) || _.isUndefined(settings.pixel_id) || _.isUndefined(settings.product_catalog_id)) return;
                }
                $.ajax({
                    method: "POST",
                    url: "dburl_ps.php",
                    data: {
                        'type': 'setFbSettings',
                        'company':'facebookExtension',
                        'mode':'config',
                        'value' : settings
                    },
                    dataType: 'text'
                }).success(function (data) {
                    console.log(data);
                    sendToFacebook('ack set merchant settings');
                    sendToFacebook('ack set pixel');
                    sendToFacebook('ack catalog');
                    //부모창 reload없이 데이터 싱크 맞추기
                    $("#pixelIdCheck").html(settings.pixel_id);
                    merchantId = settings.merchant_settings_id;
                }).error(function (e) {
                    alert(e.responseText);
                    // fail 응답 보내기
                    sendToFacebook('fail set merchant settings');
                    sendToFacebook('fail set pixel');
                    sendToFacebook('fail catalog');
                });
            }
        }

    function cancelProgressFbe() {
        $(".js-progress-fbe").hide();
        dialog_confirm('요청 취소 시 생성 중인 tsv 다운로드 파일이 모두 삭제됩니다. 진행중인 내용을 취소하고 페이지를 이동하시겠습니까?', function (result) {
            if (result) {

                if ($.browser.msie) {
                    document.execCommand("Stop");
                } else {
                    window.stop(); //works in all browsers but IE
                }

                setTimeout(function () {
                    $(".js-progress-fbe").hide();
                    $("#progressView").text("0%");
                    $("#progressViewBg").css("width", "0%");
                }, 10);

            } else {
                $(".js-progress-fbe").show();
                return false;
            }

        }, '확인', {"cancelLabel": '취소', "confirmLabel": '확인'});

    }

    //-->
</script>
<script type="text/html" id="progressFbe">
    <div class="js-progress-fbe" style="position:absolute;width:100%;height:100%;top:0px;background:#000000;z-index:1060;opacity:0.5;display:none;"></div>
    <div class="js-progress-fbe" style="left:50%;top:50%;width:300px;background:#fff;margin:-150px 0 0 -75px; auto;position:absolute;z-index:1070;padding:20px;text-align:center;display:none;">페이스북 상품피드 생성 중입니다.<br/> 잠시만 기다려주세요.
        <p style="font-size:22px;" id="progressView">0%</p>
        <div style="widtht:260px;height:18px;background:#ccc;margin-bottom:10px;">
            <div id="progressViewBg" style="height:100%">&nbsp;</div>
        </div>
        <a onclick="cancelProgressFbe()" style="cursor:pointer">요청 취소</a>
    </div>
</script>