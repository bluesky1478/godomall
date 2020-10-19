<form name="insgoForm" id="insgoForm" method="post" action="./insgo_widget_ps.php" target="ifrmProcess">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list js-btn-list" />
            <input type="button" value="미리보기" class="btn btn-white js-btn-preview" />
            <input type="submit" value="저장" class="btn btn-red" />
        </div>
    </div>

    <h4 class="table-title">인스고위젯 안내</h4>
    <div class="panel pd10">
        <p><b>인스고위젯을 사용하려면?</b></p>
        <p>1. 쇼핑몰의 인스타그램 계정을 생성하고 콘텐츠를 등록해주세요. 콘텐츠를 등록할 때 사람들이 많이 사용하는 해시태그를 함께 등록해 주시면 효과적입니다.</p>
        <p>2. 인스고위젯 설정에서 쇼핑몰에 삽입될 위젯을 생성해주세요.</p>
        <p>3. 미리보기를 통해 위젯을 확인하고 소스를 복사해주세요.</p>
        <p>4. 디자인관리에서 위젯이 노출될 페이지에 복사된 소스를 삽입해주세요.</p>
        <p>** 코드 생성 후 추가된 이미지는 1시간 간격으로 갱신됩니다.</p>
        <div class="notice-info">인스타그램 정책에 따라 일 통신횟수가 제한될 수 있으며, 어드민 미리보기 갱신 역시 일정시간(최소 1시간) 후 가능합니다.
        </div>
        <div class="notice-danger">2020년 3월 2일부터 액세스토큰 사용이 중지됨에 따라 2월 26일부터 인스타그램 아이디를 입력해야 인스고위젯 사용이 가능합니다.</div>
        <div class="notice-danger">인스타그램 아이디가 비공개 일 경우 미리보기 및 쇼핑몰에서 위젯이 동작하지 않습니다.</div>
    </div>

    <h4 class="table-title">인스고위젯 설정</h4>
    <div class="form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th class="require">위젯명</th>
                <td>
                    <label for="widgetName" title="위젯명"><input type="text" name="widgetName" id="widgetName" value="<?=$data['widgetName']?>" class="form-control js-maxlength required-common width-2xl" maxlength="30" alt="위젯명" /></label>
                </td>
            </tr>
            <tr>
                <th class="require">인스타그램 아이디</th>
                <td>
                    <input type="hidden" name="widgetSno" id="widgetSno" value="<?=$data['widgetSno']?>" />
                    <input type="hidden" name="mode" id="mode" value="<?=$mode?>" />
                    <input type="text" name="widgetInstagramId" id="widgetInstagramId" value="<?=$data['widgetInstagramId']?>" class="instagram_id_txt form-control required-common width-2xl" alt="인스타그램 아이디" placeholder="인스타그램 아이디를 입력해주세요." />
                </td>
            </tr>
            <?php
            if($data['widgetSno']) {
                ?>
                <tr>
                    <th>치환코드보기</th>
                    <td>
                        {=includeWidget('proc/_insgo.html', 'sno', '<?=$data['widgetSno']?>')}
                        <a href="#" class="btn btn-sm btn-white btn-copy js-clipboard" data-clipboard-text="{=includeWidget('proc/_insgo.html', 'sno', '<?=$data['widgetSno']?>')}" title="<?=$data['widgetName']?>">복사</a>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <th>위젯타입</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetDisplayType" value="grid" <?=$data['widgetDisplayType']['grid']?>> 그리드 타입
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetDisplayType" value="scroll" <?=$data['widgetDisplayType']['scroll']?>> 스크롤 타입
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetDisplayType" value="slide" <?=$data['widgetDisplayType']['slide']?>> 슬라이드쇼 타입
                    </label>
                </td>
            </tr>
            <tr class="widget-common widget-grid">
                <th class="require">레이아웃</th>
                <td>
                    <div>
                        <input type="text" name="widgetWidthCount" id="widgetWidthCount" value="<?=$data['widgetWidthCount']?>" class="form-control required-grid only-number" maxlength="1" value="4" alt="레이아웃" /> X
                        <input type="text" name="widgetHeightCount" id="widgetHeightCount" value="<?=$data['widgetHeightCount']?>" class="form-control required-grid only-number" maxlength="1" value="3" alt="레이아웃" />
                    </div>
                    <div class="notice-info">이미지는 최대 12개까지 노출할 수 있으며, 1줄당 최대 이미지 노출 개수는 9개입니다.</div>
                </td>
            </tr>
            <tr>
                <th class="require">썸네일 사이즈</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetThumbnailSize" value="auto" class="required-common" <?=$data['widgetThumbnailSize']['auto']?>> 페이지에 자동맞춤
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetThumbnailSize" value="hand" class="required-common" <?=$data['widgetThumbnailSize']['hand']?>>
                        수동설정 <input type="text" name="widgetThumbnailSizePx" id="widgetThumbnailSizePx" value="<?=$data['widgetThumbnailSizePx']?>" class="form-control only-number" maxlength="3" alt="썸네일사이즈" /> px
                    </label>
                    <div class="notice-info">페이지에 자동맞춤으로 설정 시 위젯이 삽입된 페이지에 맞게 썸네일 이미지 사이즈가 자동조절 됩니다.</div>
                </td>
            </tr>
            <tr>
                <th>이미지 테두리</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetThumbnailBorder" value="n" <?=$data['widgetThumbnailBorder']['n']?>> 표시안함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetThumbnailBorder" value="y" <?=$data['widgetThumbnailBorder']['y']?>> 표시함
                    </label>
                </td>
            </tr>
            <tr class="widget-common widget-scroll">
                <th>위젯 가로사이즈</th>
                <td>
                    <input type="text" name="widgetWidth" id="widgetWidth" value="<?=$data['widgetWidth']?>" class="form-control required-scroll only-number" maxlength="4" value="700" alt="위젯가로사이즈" /> px
                </td>
            </tr>
            <tr class="widget-common widget-scroll">
                <th>자동스크롤</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetAutoScroll" value="auto" <?=$data['widgetAutoScroll']['auto']?>> 자동
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetAutoScroll" value="fixed" <?=$data['widgetAutoScroll']['fixed']?>> 고정(좌우 전환 버튼에 마우스 오버 시 스크롤 됨)
                    </label>
                </td>
            </tr>
            <tr class="widget-common widget-grid-slide">
                <th>위젯 배경색</th>
                <td>
                    <input type="text" name="widgetBackgroundColor" id="widgetBackgroundColor" value="<?=$data['widgetBackgroundColor']?>" class="form-control width-xs center color-selector" readonly maxlength="7" />
                </td>
            </tr>
            <tr class="widget-common widget-scroll-slide">
                <th>전환속도 선택</th>
                <td>
                    <select name="widgetScrollSpeed">
                        <option value="fast" <?=$data['widgetScrollSpeed']['fast']?>>빠르게</option>
                        <option value="normal" <?=$data['widgetScrollSpeed']['normal']?>>보통</option>
                        <option value="slow" <?=$data['widgetScrollSpeed']['slow']?>>느리게</option>
                    </select>
                </td>
            </tr>
            <tr class="widget-common widget-slide">
                <th>전환시간 선택</th>
                <td>
                    <select name="widgetScrollTime">
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
                    <input type="text" name="widgetImageMargin" id="widgetImageMargin" value="<?=$data['widgetImageMargin']?>" class="form-control required-grid only-number" maxlength="3" value="5" alt="이미지간격" /> px
                </td>
            </tr>
            <tr class="widget-common widget-slide">
                <th>효과 선택</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="widgetEffect" value="slide" <?=$data['widgetEffect']['slide']?>> 슬라이드
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetEffect" value="fade" <?=$data['widgetEffect']['fade']?>> 페이드
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
                                            <input type="text" name="widgetSideButtonColor" id="widgetSideButtonColor" value="<?=$data['widgetSideButtonColor']?>" maxlength="7" readonly class="form-control width-xs center color-selector" />
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
                        <input type="radio" name="widgetOverEffect" value="n" <?=$data['widgetOverEffect']['n']?>> 효과없음
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetOverEffect" value="blurPoint" <?=$data['widgetOverEffect']['blurPoint']?>> 선택한 상품만 흐리게
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="widgetOverEffect" value="blurException" <?=$data['widgetOverEffect']['blurException']?>> 선택한 나머지 상품 흐리게
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
            $("#widgetWidthCount").val("4");
            $("#widgetHeightCount").val("3");
            $("#widgetBackgroundColor").val("#ffffff");
            $("#widgetSideButtonColor").val("#ffffff");
            $(".color-selector").attr("style", "background-color:#ffffff;");
            $("input:radio[name=widgetThumbnailSize]:input[value='auto']").prop("checked", true);
            $("input:radio[name=widgetThumbnailBorder]:input[value='y']").prop("checked", true);
            $("input:radio[name=widgetOverEffect]:input[value='n']").prop("checked", true);
            $("#widgetWidth").val("700");
            $("input:radio[name=widgetAutoScroll]:input[value='auto']").prop("checked", true);
            $("select[name=widgetScrollSpeed]").val("normal").prop("selected", "selected")
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

        // 목록
        $(".js-btn-list").click(function() {
            location.href = "./insgo_widget_list.php";
        });

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
            if($("mode").val() == 'regist'){
                resetData();
            }
            $(".widget-common").hide();
            $(".scroll-fixed").hide();
            if(widgetType == 'grid') {
                $(".widget-grid").show();
                $(".widget-grid-slide").show();
                checkboxChange("thumbnail", "grid");
            } else if(widgetType == 'scroll') {
                $(".widget-scroll").show();
                $(".widget-scroll-slide").show();
                checkboxChange("thumbnail", "scroll");
            } else if(widgetType == 'slide') {
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
                $.get('./insgo_widget_preview.php', param, function(data){

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
        $("select[name=widgetScrollSpeed]").val("normal").prop("selected", "selected")
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
</script>
