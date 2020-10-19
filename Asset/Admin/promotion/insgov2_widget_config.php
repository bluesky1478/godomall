<form name="insgoForm" id="insgoForm" method="post" action="./insgov2_widget_ps.php" target="ifrmProcess">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="미리보기" class="btn btn-white js-btn-preview" />
            <input type="submit" value="저장" class="btn btn-red" />
            <input type="hidden" name="mode" value="<?=$mode;?>" />
        </div>
    </div>

    <h4 class="table-title">인스고위젯 설정</h4>
    <div class="panel pd10">
        <p><b>인스고위젯을 사용하려면?</b></p>
        <p>1. 쇼핑몰의 인스타그램 계정을 생성하고 콘텐츠를 등록해주세요. 콘텐츠를 등록할 때 사람들이 많이 사용하는 해시태그를 함께 등록해 주시면 효과적입니다.</p>
        <p>2. 인스고위젯 설정에서 쇼핑몰에 삽입될 위젯을 생성해주세요.</p>
        <p>3. 미리보기를 통해 위젯을 확인하고 소스를 복사해주세요.</p>
        <p>4. 디자인관리에서 위젯이 노출될 페이지에 복사된 소스를 삽입해주세요.</p>
        <p>** 코드 생성 후 추가된 이미지는 1시간 간격으로 갱신됩니다.</p>
        <div class="notice-info">인스타그램 정책에 따라 일 통신횟수가 제한될 수 있으며, 어드민 미리보기 갱신 역시 일정시간(최소 1시간) 후 가능합니다.
        </div>
        <div class="notice-danger">API 연결 중 토큰 확인이 되지 않을 경우 자동 연동 해제되며, 게시물 노출이 되지 않습니다.</div>
    </div>

    <div class="form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th class="require">인스고위젯 연결</th>
                <td>
                    <input type="hidden" name="insgoInterlock" value="<?=$data['insgoInterlock']?>">
                    <input type="hidden" name="widgetAccessToken" value="<?=$data['widgetAccessToken']?>">
                    <?php
                    if($data['widgetAccessToken']) {
                        ?>
                        <input type="button" class="btn btn-sm btn-gray js-connect-api-cancel" onclick="insgoPopup('c')" value="인스고위젯 연동 해제"/>
                        <?php
                    } else{ ?>
                        <input type="button" class="btn btn-sm btn-gray js-connect-api-connect" onclick="insgoPopup('s')" value="인스고위젯 연동"/>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>치환코드보기</th>
                <td>
                    <input type="hidden" name="widgetSno" id="widgetSno" value="<?=$data['widgetSno']?>" />
                    <?php
                    if($data['widgetSno']) {
                        ?>
                        {=includeWidget('proc/_insgov2.html', 'sno', '<?=$data['widgetSno']?>')}
                        <a href="#" class="btn btn-sm btn-white btn-copy js-clipboard" data-clipboard-text="{=includeWidget('proc/_insgov2.html', 'sno', '<?=$data['widgetSno']?>')}" >복사</a>
                        <input type="hidden" name="sno" value="<?=$data['widgetSno']?>">
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>위젯타입</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetDisplayType" value="grid" <?=$data['widgetDisplayType']['grid']?> <?=$disabled;?>> 그리드 타입
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetDisplayType" value="scroll" <?=$data['widgetDisplayType']['scroll']?> <?=$disabled;?>> 스크롤 타입
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetDisplayType" value="slide" <?=$data['widgetDisplayType']['slide']?> <?=$disabled;?>> 슬라이드쇼 타입
                    </label>
                </td>
            </tr>
            <tr class="widget-common widget-grid">
                <th class="require">레이아웃</th>
                <td>
                    <div>
                        <input type="text" name="widgetWidthCount" id="widgetWidthCount" value="<?=$data['widgetWidthCount']?>" class="form-control required-grid only-number" maxlength="1" alt="레이아웃" <?=$disabled;?> /> X
                        <input type="text" name="widgetHeightCount" id="widgetHeightCount" value="<?=$data['widgetHeightCount']?>" class="form-control required-grid only-number" maxlength="1" alt="레이아웃" <?=$disabled;?> />
                    </div>
                    <div class="notice-info">이미지는 최대 25개까지 노출할 수 있으며, 1줄당 최대 이미지 노출 개수는 9개입니다.</div>
                </td>
            </tr>
            <tr>
                <th class="require">썸네일 사이즈</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetThumbnailSize" value="auto" class="required-common" <?=$data['widgetThumbnailSize']['auto']?> <?=$disabled;?>> 페이지에 자동맞춤
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetThumbnailSize" value="hand" class="required-common" <?=$data['widgetThumbnailSize']['hand']?> <?=$disabled;?>>
                        수동설정 <input type="text" name="widgetThumbnailSizePx" id="widgetThumbnailSizePx" value="<?=$data['widgetThumbnailSizePx']?>" <?=$disabled;?> class="form-control only-number" maxlength="3" alt="썸네일사이즈" /> px
                    </label>
                    <div class="notice-info">페이지에 자동맞춤으로 설정 시 위젯이 삽입된 페이지에 맞게 썸네일 이미지 사이즈가 자동조절 됩니다.</div>
                </td>
            </tr>
            <tr>
                <th>이미지 테두리</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetThumbnailBorder" value="n" <?=$data['widgetThumbnailBorder']['n']?> <?=$disabled;?>> 표시안함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetThumbnailBorder" value="y" <?=$data['widgetThumbnailBorder']['y']?> <?=$disabled;?>> 표시함
                    </label>
                </td>
            </tr>
            <tr class="widget-common widget-scroll">
                <th>위젯 가로사이즈</th>
                <td>
                    <input type="text" name="widgetWidth" id="widgetWidth" value="<?=$data['widgetWidth']?>" class="form-control required-scroll only-number" maxlength="4" value="700" alt="위젯가로사이즈" <?=$disabled;?> /> px
                </td>
            </tr>
            <tr class="widget-common widget-scroll">
                <th>자동스크롤</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetAutoScroll" value="auto" <?=$data['widgetAutoScroll']['auto']?> <?=$disabled;?>> 자동
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetAutoScroll" value="fixed" <?=$data['widgetAutoScroll']['fixed']?> <?=$disabled;?>> 고정(좌우 전환 버튼에 마우스 오버 시 스크롤 됨)
                    </label>
                </td>
            </tr>
            <tr class="widget-common widget-grid-slide">
                <th>위젯 배경색</th>
                <td>
                    <input type="text" name="widgetBackgroundColor" id="widgetBackgroundColor" value="<?=$data['widgetBackgroundColor']?>" class="form-control width-xs center color-selector" readonly maxlength="7" <?=$disabled;?> />
                </td>
            </tr>
            <tr class="widget-common widget-scroll-slide">
                <th>전환속도 선택</th>
                <td>
                    <select name="widgetScrollSpeed">
                        <option value="fast" <?=$data['widgetScrollSpeed']['fast']?> <?=$disabled;?>>빠르게</option>
                        <option value="normal" <?=$data['widgetScrollSpeed']['normal']?> <?=$disabled;?>>보통</option>
                        <option value="slow" <?=$data['widgetScrollSpeed']['slow']?> <?=$disabled;?>>느리게</option>
                    </select>
                </td>
            </tr>
            <tr class="widget-common widget-slide">
                <th>전환시간 선택</th>
                <td>
                    <select name="widgetScrollTime" <?=$disabled;?>>
                        <?php
                        for($i=1 ; $i<6 ; $i++) {
                            echo '<option value="' . $i . '" ' . $data['widgetScrollTime'][$i] . '>' . $i . '초</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr class="widget-common widget-grid">
                <th class="require">이미지 간격</th>
                <td>
                    <input type="text" name="widgetImageMargin" id="widgetImageMargin" value="<?=$data['widgetImageMargin']?>" class="form-control required-grid only-number" maxlength="3" value="5" alt="이미지간격" <?=$disabled;?> /> px
                </td>
            </tr>
            <tr class="widget-common widget-slide">
                <th>효과 선택</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetEffect" value="slide" <?=$data['widgetEffect']['slide']?> <?=$disabled;?>> 슬라이드
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetEffect" value="fade" <?=$data['widgetEffect']['fade']?> <?=$disabled;?>> 페이드
                    </label>
                </td>
            </tr>
            <tr class="scroll-fixed">
                <th>좌우 전환 버튼</th>
                <td>
                    <div id="bannerSideButtonConf" class="form-inline mgt10 mgl10">
                        <table class="table table-cols">
                            <colgroup>
                                <col class="width-xs"/>
                                <col/>
                            </colgroup>
                            <tr>
                                <th>색상</th>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="text" name="widgetSideButtonColor" id="widgetSideButtonColor" value="<?=$data['widgetSideButtonColor']?>" maxlength="7" readonly class="form-control width-xs center color-selector" <?=$disabled;?> />
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr>
                <th>마우스 오버 시 효과</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetOverEffect" value="n" <?=$data['widgetOverEffect']['n']?> <?=$disabled;?>> 효과없음
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetOverEffect" value="blurPoint" <?=$data['widgetOverEffect']['blurPoint']?> <?=$disabled;?>> 선택한 상품만 흐리게
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetOverEffect" value="blurException" <?=$data['widgetOverEffect']['blurException']?> <?=$disabled;?>> 선택한 나머지 상품 흐리게
                    </label>
                    <div class="notice-info">PC쇼핑몰 화면에서만 적용됩니다.</div>
                </td>
            </tr>
        </table>
    </div>
</form>
<style>
    .widget-grid, .widget-scroll, .widget-scroll-slide, .widget-slide, .scroll-fixed { display:none; }
</style>
<script type="text/javascript">
    $(function () {
        if($("#widgetSno").val()) {
            $("mode").val("modify");
        } else {
            $("input:radio[name=widgetDisplayType]:input[value='grid']").prop("checked", true);
            $("#widgetWidthCount").val("6");
            $("#widgetHeightCount").val("4");
            $("#widgetBackgroundColor").val("#ffffff");
            $("#widgetSideButtonColor").val("#ffffff");
            $(".color-selector").attr("style", "background-color:#ffffff;");
            $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("checked", true);
            $("input:radio[name=widgetThumbnailBorder]:input[value='y']").prop("checked", true);
            $("input:radio[name=widgetOverEffect]:input[value='n']").prop("checked", true);
            $("#widgetWidth").val("700");
            $("input:radio[name=widgetAutoScroll]:input[value='auto']").prop("checked", true);
            $("select[name=widgetScrollSpeed]").val("normal").prop("selected", "selected");
            $("select[name=widgetScrollTime]").val("3").prop("selected", "selected");
            $("input:radio[name=widgetEffect]:input[value='slide']").prop("checked", true);
            $("#widgetImageMargin").val(5);
        }

        // 위젯 타입별 기본값 노출
        var displayType = $("input:radio[name=widgetDisplayType]:checked").val();
        $(".widget-common").hide();
        $(".widget-" + displayType).show();
        switch(displayType) {
            case 'grid':
                $(".widget-grid-slide").show();
                break;
            case 'scroll':
                $(".widget-scroll-slide").show();
                if($("input:radio[name=widgetAutoScroll]:checked").val() == 'fixed') {
                    $(".scroll-fixed").show();
                }
                break;
            case 'slide':
                $(".widget-scroll-slide").show();
                $(".widget-grid-slide").show();
                checkboxChange("thumbnail", "slide");
                break;
        }

        $(".btn-copy").click(function(e){
            e.preventDefault();
        });

        $("input[name=widgetThumbnailSize]").change(function() {
            checkboxChange("thumbnail", $(this).val());
        });

        $("#widgetThumbnailSizePx").focusin(function() {
            checkboxChange("thumbnail", "hand");
        });

        // 위젯 타입 변경 이벤트
        $("input:radio[name=widgetDisplayType]").click(function() {
            var widgetType = $(this).val();
            var configDisplayType = '<?=$displayType;?>';

            if($("mode").val() == 'regist'){
                resetData();
            }
            $(".widget-common").hide();
            $(".scroll-fixed").hide();

            if(widgetType == 'grid') {
                // 위젯 타입 변경 시, 기본값 재 설정
                if(configDisplayType !== 'grid'){
                    $("#widgetWidthCount").val("6");
                    $("#widgetHeightCount").val("4");
                    $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("checked", true);
                    $("#widgetThumbnailSizePx").val("");
                    $("input:radio[name=widgetThumbnailBorder]:input[value='y']").prop("checked", true);
                    $("#widgetBackgroundColor").val("#ffffff");
                    $(".color-selector-btn").attr("style", "background-color:#ffffff;");
                    $("#widgetImageMargin").val(5);
                    $("input:radio[name=widgetOverEffect]:input[value='n']").prop("checked", true);
                }else{
                    $("#widgetWidthCount").val(<?=$widthCount;?>);
                    $("#widgetHeightCount").val(<?=$heightCount;?>);
                    $("input:radio[name=widgetThumbnailSize]:input[value='<?=$thumbnailSize;?>']").prop("checked", true);
                    $("#widgetThumbnailSizePx").val(<?=$widgetThumbnailSizePx;?>);
                    $("input:radio[name=widgetThumbnailBorder]:input[value='<?=$thumbnailBorder;?>']").prop("checked", true);
                    $("#widgetBackgroundColor").val("<?=$backgroundColor;?>");
                    $(".color-selector-btn").attr("style", "background-color:<?=$backgroundColor;?>;");
                    $("#widgetImageMargin").val(<?=$ImageMargin;?>);
                    $("input:radio[name=widgetOverEffect]:input[value='<?=$overEffect;?>']").prop("checked", true);
                }
                $(".widget-grid").show();
                $(".widget-grid-slide").show();
                checkboxChange("thumbnail", "grid");
            } else if(widgetType == 'scroll') {
                // 위젯 타입 변경 시, 기본값 재 설정
                if(configDisplayType !== 'scroll'){
                    $("input:radio[name=widgetThumbnailSize]:input[value='hand']").prop("checked", true);
                    $("input:radio[name=widgetThumbnailBorder]:input[value='y']").prop("checked", true);
                    $("#widgetWidth").val("700");
                    $(".color-selector-btn").attr("style", "background-color:#ffffff;");
                    $("input:radio[name=widgetAutoScroll]:input[value='auto']").prop("checked", true);
                    $("select[name=widgetScrollSpeed]").val("normal").prop("selected", "selected");
                    $("input:radio[name=widgetOverEffect]:input[value='n']").prop("checked", true);
                }else{
                    $("input:radio[name=widgetThumbnailSize]:input[value='<?=$thumbnailSize;?>']").prop("checked", true);
                    $("#widgetThumbnailSizePx").val("<?=$thumbnailSizePx;?>");
                    $("input:radio[name=widgetThumbnailBorder]:input[value='<?=$thumbnailBorder;?>']").prop("checked", true);
                    $("#widgetWidth").val("<?=$width;?>");
                    $("input:radio[name=widgetAutoScroll]:input[value='<?=$autoScroll;?>']").prop("checked", true);
                    if($("input:radio[name=widgetAutoScroll]").val() == 'fixed'){
                        $("#widgetSideButtonColor").val("<?=$sideButtonColor;?>");
                        $(".color-selector-btn").attr("style", "background-color:<?=$sideButtonColor;?>;");
                    }
                    $("select[name=widgetScrollSpeed]").val("<?=$scrollSpeed;?>").prop("selected", "selected");
                    $("input:radio[name=widgetOverEffect]:input[value='<?=$overEffect;?>']").prop("checked", true);
                }
                $(".widget-scroll").show();
                $(".widget-scroll-slide").show();
                checkboxChange("thumbnail", "scroll");
            } else if(widgetType == 'slide') {
                // 위젯 타입 변경 시, 기본값 재 설정
                if(configDisplayType !== 'slide'){
                    $("input:radio[name=widgetThumbnailSize]:input[value='hand']").prop("checked", true);
                    $("input:radio[name=widgetThumbnailBorder]:input[value='y']").prop("checked", true);
                    $("#widgetThumbnailSizePx").val("");
                    $("#widgetBackgroundColor").val("#ffffff");
                    $(".color-selector-btn").attr("style", "background-color:#ffffff;");
                    $("select[name=widgetScrollSpeed]").val("normal").prop("selected", "selected");
                    $("select[name=widgetScrollTime]").val("3").prop("selected", "selected");
                    $("input:radio[name=widgetEffect]:input[value='slide']").prop("checked", true);
                    $("input:radio[name=widgetOverEffect]:input[value='n']").prop("checked", true);
                }else{
                    $("input:radio[name=widgetThumbnailSize]:input[value='<?=$thumbnailSize;?>']").prop("checked", true);
                    $("input:radio[name=widgetThumbnailBorder]:input[value='<?=$thumbnailBorder;?>']").prop("checked", true);
                    $("#widgetBackgroundColor").val("#<?=$backgroundColor;?>");
                    $(".color-selector-btn").attr("style", "background-color:<?=$backgroundColor;?>;");
                    $("select[name=widgetScrollSpeed]").val("<?=$scrollSpeed;?>").prop("selected", "selected");
                    $("select[name=widgetScrollTime]").val("<?=$scrollTime;?>").prop("selected", "selected");
                    $("input:radio[name=widgetEffect]:input[value='<?=$effect;?>']").prop("checked", true);
                    $("input:radio[name=widgetOverEffect]:input[value='<?=$overEffect;?>']").prop("checked", true);
                }
                checkboxChange("effect", "slide");
                checkboxChange("thumbnail", "slide");
                $(".widget-slide").show();
                $(".widget-scroll-slide").show();
                $(".widget-grid-slide").show();
            }
            $.each($('.required-' + widgetType), function(){
                $(this).closest('tr').find('th').addClass('require');
            });
        });

        $("input:radio[name=widgetAutoScroll]").click(function() {
            var scrollType = $(this).val();
            if(scrollType == 'fixed') {
                $(".scroll-fixed").show();
                $("#widgetSideButtonColor").val("#ffffff");
            } else {
                $(".scroll-fixed").hide();
            }
        });

        // 숫자만 입력 가능
        $(".only-number").keyup(function() {
            $(this).val( $(this).val().replace(/[^0-9]/gi,"") );
        });

        // 저장
        $("#insgoForm").submit(function() {
            if (insgoValid() == false) {
                return false;
            } else {}
        });

        /**
         * 인스고위젯 미리보기
         *
         */
        $('.js-btn-preview').click(function(e) {
            $('.js-btn-preview').prop('disabled', true);
            if (insgoValid() == true) {
                var title = "인스고위젯 미리보기";
                var param = $('#insgoForm').serialize();
                $.get('./insgov2_widget_preview.php', param, function(data){

                    data = '<div id="viewInfoForm">'+data+'</div>';

                    var layerForm = data;

                    BootstrapDialog.show({
                        title:title,
                        size: get_layer_size('wide-lg'),
                        message: $(layerForm),
                        closable: true
                    });
                });
            }
        });

        $(document).on('click', '.modal, .modal .close', function(){
            $('.js-btn-preview').prop('disabled', false);
        });

        $(document).click(function(e){ //문서 body를 클릭했을때
            if(e.target.className =="popupInsgo"){return false} //내가 클릭한 요소(target)를 기준으로 상위요소에 .share-pop이 없으면 (갯수가 0이라면)
            $(".share-pop").stop().fadeOut(500);
        });
    });

    function insgoValid() {
        var checkedType = $("input:radio[name=widgetDisplayType]:checked").val();
        var patt = /\s/g;

        if(patt.test($("#widgetInstagramId").val()) === true) {
            alert("띄어쓰기는 허용되지 않습니다.");
            return false;
        }

        var pass = true;
        $.each($(".required-common"), function() {
            if($(this).val() == '') {
                alert($(this).attr("alt") + "값을 입력해주세요.");
                $(this).focus();
                pass = false;
                return false;
            } else {
                if($(this).attr("name") == 'widgetThumbnailSize' && $('input[name="widgetThumbnailSize"]').eq(1).is(':checked') === true && $('input[name="widgetThumbnailSizePx"]').val() == '') {
                    alert($('input[name="widgetThumbnailSizePx"]').attr("alt") + "값을 입력해주세요.");
                    $('input[name="widgetThumbnailSizePx"]').focus();
                    pass = false;
                    return false;
                }
            }
        });

        if(pass) {
            $.each($(".required-" + checkedType), function () {
                if($(this).val() == '') {
                    alert($(this).attr("alt") + "값을 입력해주세요..");
                    $(this).focus();
                    pass = false;
                    return false;
                }
            });
        }

        if(!pass) {
            return false;
        }

        return true;
    }

    function resetData(type) {
        $("#widgetBackgroundColor").val("#ffffff");
        $("#widgetSideButtonColor").val("#ffffff");
        $(".color-selector").attr("style", "background-color:#ffffff;");
        $("#widgetImageMargin").val("");
        $("#widgetThumbnailSizePx").val("");
        $("#widgetWidth").val("");

        switch(type){
            case "grid":
                $("#widgetWidthCount").val("4");
                $("#widgetHeightCount").val("3");
                break;
            case "scroll":
                $("#widgetWidthCount").val("");
                $("#widgetHeightCount").val("");
                $("#widgetWidth").val("700");
                break;
            case "slide":
                $("#widgetWidthCount").val("");
                $("#widgetHeightCount").val("");
                break;
            default:
                break;
        }
        $("input:radio[name=widgetDisplayType]:input[value='grid']").prop("checked", true);
        $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("checked", true);
        $("input:radio[name=widgetThumbnailBorder]:input[value='y']").prop("checked", true);
        $("input:radio[name=widgetOverEffect]:input[value='n']").prop("checked", true);
        $("input:radio[name=widgetAutoScroll]:input[value='auto']").prop("checked", true);
        $("select[name=widgetScrollSpeed]").val("normal").prop("selected", "selected");
        $("select[name=widgetScrollTime]").val("3").prop("selected", "selected");
        $("input:radio[name=widgetEffect]:input[value='slide']").prop("checked", true);
    }

    function checkboxChange(kind, status) {
        switch(kind) {
            case "thumbnail":
                if(status == 'auto' || status == 'grid') {
                    if($("mode").val() == 'regist'){
                        $("#widgetThumbnailSizePx").val("");
                    }
                    $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("checked", true);
                    $("input:radio[name=widgetThumbnailSize]:input[value='hand']").prop("checked", false);
                    $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("disabled", false);
                    $("input:radio[name=widgetOverEffect]:input[value='blurPoint']").prop("disabled", false);
                    $("input:radio[name=widgetOverEffect]:input[value='blurException']").prop("disabled", false);
                } else if(status == 'hand') {
                    $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("checked", false);
                    $("input:radio[name=widgetThumbnailSize]:input[value='hand']").prop("checked", true);
                    if($("input:radio[name=widgetDisplayType]:checked").val() != 'slide') {
                        $("input:radio[name=widgetOverEffect]:input[value='blurPoint']").prop("disabled", false);
                        $("input:radio[name=widgetOverEffect]:input[value='blurException']").prop("disabled", false);
                    }
                } else if(status == 'scroll') {
                    $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("checked", false);
                    $("input:radio[name=widgetThumbnailSize]:input[value='hand']").prop("checked", true);
                    $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("disabled", true);
                    $("input:radio[name=widgetOverEffect]:input[value='blurPoint']").prop("disabled", false);
                    $("input:radio[name=widgetOverEffect]:input[value='blurException']").prop("disabled", false);
                    if($("input:radio[name=widgetAutoScroll]:checked").val() == 'fixed') {
                        $(".scroll-fixed").show();
                    }
                } else if(status == 'slide') {
                    $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("checked", false);
                    $("input:radio[name=widgetThumbnailSize]:input[value='hand']").prop("checked", true);
                    $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("disabled", true);
                    $("input:radio[name=widgetOverEffect]:input[value='n']").prop("checked", true);
                    $("input:radio[name=widgetOverEffect]:input[value='blurPoint']").prop("disabled", true);
                    $("input:radio[name=widgetOverEffect]:input[value='blurException']").prop("disabled", true);
                }
                break;
            case "effect":
                var effectVal = $("input:radio[name=widgetEffect]:checked").val();
                if(effectVal == "" || effectVal == undefined) {
                    $("input:radio[name=widgetEffect]:input[value='" + status + "']").prop("checked", true);
                }
                break;
        }
    }

    function insgoPopup(mode) {
        if ($('#insgoForm').data('lock-write') == '1') {
            alert('인스고위젯관리v2의 쓰기 권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.');
            return false;
        }
        if (mode == 's') {
            window.open('../promotion/insgo_request_ps.php?mode=setShopInfo&agreeFl=y&shopSno=<?=$shopSno?>', 'popupInsgo', 'width=600,height=600,location=no,scrollbars=yes');
        } else if (mode == 'c') {
            var params = {
                'mode': 'insgoInfoDelete',
                'shopSno': '<?=$shopSno;?>'
            };
            $.ajax({
                method: "POST",
                cache: false,
                url: '../promotion/insgo_request_ps.php',
                data: params,
                success: function (data) {
                    location.reload();
                },
                error: function (request, status, error) {
                    alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                }
            });
        }
    }
</script>
