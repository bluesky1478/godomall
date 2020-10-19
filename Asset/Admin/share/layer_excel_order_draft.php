<!-- //@formatter:off -->
<form id="frmExcelForm" name="frmExcelForm" action="../share/layer_excel_ps.php" method="post">
    <input type="hidden" name="mode" value="excel" >
    <input type="hidden" name="searchCount" value="<?=$searchCount?>" >
    <input type="hidden" name="totalCount" value="<?=$totalCount?>" >
    <input type="hidden" name="menu" value="orderDraft" />
    <input type="hidden" name="location" value="" />
    <input type="hidden" name="whereDetail" value="" >

    <div id="whereDetail" style="display:none"></div>
    <div class="table-title gd-help-manual">
        다운로드 양식 검색
    </div>
    <table class="table table-cols no-title-line">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>다운로드 범위</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="whereFl" value="select">선택내역</label>
                    <label class="radio-inline"><input type="radio" name="whereFl" value="search">검색내역</label>
                </div>
            </td>
            <th>옵션 다운로드 방식</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="optionCountFl" value="total" checked>옵션별 총수량</label>
                    <label class="radio-inline"><input type="radio" name="optionCountFl" value="per">옵션별 수량 1개씩</label>
                </div>
            </td>
        </tr>
        <tr>
            <th>양식 선택</th>
            <td colspan="3">
                <div class="form-inline">
                    <select name="formSno" class="form-control width-xl">
                        <option value="">선택</option>
                        <?php if ($menu != 'board') {
                            foreach ($formList as $k => $v) { ?>
                                <option value="<?= $v['sno'] ?>" data-count-personal-field="<?= $v['countPersonalField'] ?>"><?= $v['title'] ?></option>
                            <?php }
                        } ?>
                    </select>
                    <input type="button" id="btnAddOrderDraft" class='btn btn-gray btn-sm' value="다운로드 양식 추가" />
                    <input type="button" id="btnManageOrderDraft" class='btn btn-gray btn-sm' value="다운로드 양식 관리" />
                </div>
            </td>
        </tr>
        <tr>
            <th>파일명</th>
            <td colspan="3">
                <div class="form-inline">
                   <input type="text" name="downloadFileName"  class="form-control width-xl js-type-korea js-maxlength" maxlength="50"></div>
            </td>
        </tr>
        <tr>
            <th>비밀번호 사용여부</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="passwordFl" value="y" checked="checked">
                        사용
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="passwordFl" value="n">
                        사용하지않음
                    </label>
                </div>
                <p class="notice-info notice-required-password display-none">다운로드 항목 중 개인정보가 3개 이상 포함되는 경우, 비밀번호 설정이 필수입니다.</p>
            </td>
            <th>엑셀파일 당<br/>
                데이터 최대개수
            </th>
            <td>
                <div class="form-inline">
                <label>
                    <input type="text" name="excelPageNum"  class="form-control width-2xs" value="10000"> 개
                </label>
                    </div>
                <p class="notice-info">
                다운로드 시간이 오래 걸리는 경우<br/>
                데이터 최대개수 숫자를 조정하세요
                </p>
            </td>
        </tr>
        <tr id="js-excel-form-password">
            <th>비밀번호 설정</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="password" class="form-control width-xl js-type-normal"/></label>
                </div>
            </td>
            <th>비밀번호 확인</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="rePassword" class="form-control width-xl js-type-normal"/></label>
                </div>
            </td>
        </tr>
    </table>
    <?php if ($menu != 'goods') { ?>
    <p class="notice-danger notice-info mgb30">
        개인정보를 개인용PC에 저장할 시 암호화가 의무이므로 비밀번호 ‘사용’을 권장합니다.
        <span class="btn-link-underline js-excel-guide">[자세히보기]</span>
    </p>
    <?php } ?>

    <div class="table-btn">
        <input type="submit" value="요청" class="btn btn-lg btn-black">
    </div>
</form>
<div>
<form id="frmExcelRequest" name="frmExcelRequest" action="../share/layer_excel_ps.php" method="post" target="ifrmProcess">
    <input type="hidden"  name="mode" value="download">
    <input type="hidden"  name="excelFileName" value="<?=$excelFileName?>">
    <input type="hidden"  name="sno" value="">
    <input type="hidden"  name="excelKey" value="">
    <input type="hidden"  name="excelTitle" value="">
    <input type="hidden"  name="excelDownloadReason" value="">
    <table class="table table-rows" id="tblExcelRequest">
        <thead>
        <tr>
            <th class="width-3xs">번호</th>
            <th class="width-md">다운로드 양식명</th>
            <th class="width-lg">파일명</th>
            <th class="width-3xs">파일구분</th>
            <th class="width-xs">파일상태</th>
            <th class="width-xs">요청자</th>
            <th class="width-xs">다운로드 기간</th>
            <th class="width-xs">다운로드</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    </form>
</div>
<script type="text/html" id="progressExcel">
    <div class="js-progress-excel" style="position:absolute;width:100%;height:100%;top:0px;left:0px;background:#000000;z-index:1060;opacity:0.5;display:none;"></div>
    <div class="js-progress-excel" style="width:300px;background:#fff;margin:0 auto;position:absolute;z-index:1070;top:250px;padding:20px;left:270px;text-align:center;display:none;">다운로드할 엑셀파일을 생성 중입니다.<br/> 잠시만 기다려주세요.
        <p style="font-size:22px;" id="progressView">0%</p>
        <div style="widtht:260px;height:18px;background:#ccc;margin-bottom:10px;">
            <div id="progressViewBg" style="height:100%">&nbsp;</div>
        </div>
        <a onclick="cancelProgressExcel()" style="cursor:pointer">요청 취소</a>
    </div>
</script>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('select[name=formSno]').change(function (e) {
            var $pass = $(':radio[name=passwordFl]'), $notice = $('.notice-required-password');
            $pass[1].disabled = false;
            $notice.addClass('display-none');
            if ($(e.target).find(':selected').data('count-personal-field') > 2) {
                $pass[0].checked = true;
                $pass[1].disabled = true;
                $notice.removeClass('display-none');
            }
            $pass.filter(':checked').trigger('click');
        });
        var complied = _.template($('#progressExcel').html());
        $(".modal-content").append(complied());

        $("#frmExcelForm").validate({
            dialog: false,
            submitHandler: function (form) {

                var checkPersonalField = true;
                $.ajax({
                    url: '../share/layer_excel_ps.php',
                    type: 'post',
                    async: false,
                    data: {'mode': 'countPersonalField', 'formSno': $("select[name='formSno']").val()},
                    success: function (cnt) {
                        if(cnt > 2 && $("input[name='passwordFl']:checked").val() == 'n') {
                            alert("비밀번호를 입력해주세요.");
                            var $pass = $(':radio[name=passwordFl]'), $notice = $('.notice-required-password');
                            $pass[0].checked = true;
                            $pass[1].disabled = true;
                            $notice.removeClass('display-none');
                            $pass.filter(':checked').trigger('click');
                            checkPersonalField = false;
                        }
                    }
                })
                if(!checkPersonalField) {
                    return false;
                }

                if ($("input[name='passwordFl']:checked").val() == 'y' && ($("input[name='password']").val() == '' || $("input[name='rePassword']").val() == '')) {
                    alert("비밀번호를 입력해주세요.");
                    return false;
                }

                if ($("input[name='passwordFl']:checked").val() == 'y' && $("input[name='password']").val() != $("input[name='rePassword']").val()) {
                    alert("동일한 비밀번호를 입력해주세요.");
                    return false;
                }

                var whereDetail = $('#<?=$targetForm?>').serialize();

                if ($("input[name='whereFl']:checked").val() == 'select') {

                    if ($("#<?=$targetListForm?> input[name*='<?=$targetListSno?>']:checked").length) {
                        whereDetail += "&" + $("#<?=$targetListForm?> input[name*='<?=$targetListSno?>']").serialize();
                    } else {
                        alert("리스트에서 다운받을 데이터를 먼저 선택해주세요.");
                        return false;
                    }
                }


                if ($("input[name='whereFl']:checked").val() == 'search' && $("input[name='searchCount']").val() == 0) {
                    alert("검색된 데이터가 없습니다.");
                    return false;
                }

                <?php if($orderStateMode) { ?>
                whereDetail += "&statusMode=<?=$orderStateMode?>";
                <?php } ?>

                <?php if($menu == 'board') { ?>
                <?php if (gd_is_provider()) { ?>
                whereDetail += "&bdId=" + $("input[name='bdId']").val();
                <?php } else { ?>
                whereDetail += "&bdId=" + $("select[name='bdId']").val();
                <?php } ?>
                <?php } ?>

                if ($("input[name='downloadFileName']").val().trim() == '') {
                    $("input[name='downloadFileName']").val($("select[name='formSno'] option:selected").text());
                }

                $("input[name='whereDetail']").val(whereDetail);

                if ($("input[name='excelPageNum']").val().trim() == '' || parseInt($("input[name='excelPageNum']").val().trim()) == '0') {
                    dialog_confirm('엑셀파일당 데이터 최대개수 제한이 없어 대용량 다운로드 시 시간이 오래 걸릴 수 있습니다. 다운로드 요청을 진행하시겠습니까?', function (result) {
                        if (result) {
                            form.target = 'ifrmProcess';
                            form.submit();
                            $(".js-progress-excel").show();
                        } else {
                            return false;
                        }

                    }, '확인', {"cancelLabel": '취소', "confirmLabel": '진행'});
                } else {
                    form.target = 'ifrmProcess';
                    form.submit();
                    $(".js-progress-excel").show();
                    return false;
                }
            },
            // onclick: false, // <-- add this option
            rules: {
                formSno: {
                    required: true
                }
            },
            messages: {
                formSno: {
                    required: "다운로드 양식을 선택해주세요."
                }
            }
        });


        set_excel_list();

        $('input[name="passwordFl"]').click(function (e) {
            if ($(this).val() == 'y') {
                $("#js-excel-form-password").show();
            } else {
                $("#js-excel-form-password").hide();
            }
        });

        $("input.js-type-korea").bind('keyup', function () { //익스 11 한글 초중성분리 그래서 test후 replace
            var tmp = $(this).val();
            var pattern = /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A20-9!@#$%^_{}~,.]/;
            if (pattern.test(tmp)) {
                $(this).val(tmp.replace(/[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A20-9!@#$%^_{}~,.]/g, ''));
            }
        });

        //영어랑숫자만입력
        $("input.js-type-normal").bind('keyup', function () {
            $(this).val($(this).val().replace(/[^a-z0-9!@#$%^_{}~,.]*/gi, ''));
        });


        $("input[name='excelPageNum']").bind('keyup', function () {
            $(this).val($(this).val().replace(/[^0-9]*/gi, ''));
        });


        $('input[maxlength]').maxlength({
            showOnReady: true,
            alwaysShow: true
        });

        if ($("#<?=$targetListForm?> input[name*='<?=$targetListSno?>']:checked").length) {
            $("input[name='whereFl']").eq(0).prop("checked", true);
        } else {
            $("input[name='whereFl']").eq(1).prop("checked", true);
        }

        // maxlength의 경우 display none으로 되어있으면 정상작동 하지 않는다 따라서 페이지 로딩 후 maxlength가 적용된 후 display none으로 강제 처리 (임시방편 처리)
        setTimeout(function () {
            $('#frmExcelForm').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '1px', left: '255px'});
        }, 1000);

        // 엑셀 다운로드 가이드 팝업
        $('.js-excel-guide').on('click', function () {
            BootstrapDialog.show({
                title: '관련 법적 고지 안내',
                message: '<span class="text-blue bold">방송통신위원회고시 ‘개인정보의 기술적·관리적 보호조치 기준’ 제6조 4항</span><br/><br/><span class="bold">제6조(개인정보의 암호화)</span><br/>④ 정보통신서비스 제공자등은 이용자의 개인정보를 컴퓨터, 모바일 기기 및 보조저장매체 등에 저장할 때에는 이를 암호화해야 한다.',
                buttons: [{
                    label: '확인', cssClass: 'btn-black', hotkey: 13, size: BootstrapDialog.SIZE_LARGE,
                    action: function (dialog) {
                        dialog.close();
                    }
                }]
            });
        });
    });

    function setPersonalField(count) {
        $('select[name="formSno"] option:selected').data('count-personal-field', count);
        if (count > 2) {
            $("#js-excel-form-password").show();
            $('.notice-required-password').removeClass('display-none');
            $('input[name="passwordFl"]').eq(0).prop('checked', true);
            $('input[name="passwordFl"]').eq(1).prop('disabled', true);
        } else {
            $("#js-excel-form-password").hide();
            //$('input[name="passwordFl"]').eq(0).prop('checked', true);
            $('.notice-required-password').addClass('display-none');
            $('input[name="passwordFl"]').eq(1).prop('disabled', false);
        }
    }

    function set_excel_list(msg) {
        if (msg) {
            alert(msg);
        }

        //필수정보 세팅
        $.post('../share/layer_excel_ps.php', {'mode': 'searchList', 'menu': '<?=$menu?>', 'location': '<?=$location?>'}, function (data) {

            var getData = $.parseJSON(data);
            var addHtml = "";

            if (getData != '') {
                var pageNum = 1;

                $.each(getData, function (key, val) {

                    if (val.state == 'n') var cnt = 1;
                    else var cnt = val.fileName.split('<?=STR_DIVISION?>').length;

                    for (var i = 0; i < cnt; i++) {
                        addHtml += "<tr>";
                        addHtml += "<td class='width-3xs center number'>" + pageNum + "</td>";
                        addHtml += "<td class='width-md center'>" + val.title + "</td>";
                        addHtml += "<td class='width-lg center'>" + val.downloadFileName + "</td>";
                        addHtml += "<td class='width-3xs center'>" + (i + 1) + "/" + cnt + "</td>";
                        if (val.state = 'y') {
                            addHtml += "<td class='width-xs center'>생성완료</td>";
                            if (val.managerNm === null) {
                                addHtml += "<td class='width-xs center'>고도CS담당</td>";
                            } else {
                                addHtml += "<td class='width-xs center'>" + val.managerNm + "(" + val.managerId + ")</td>";
                            }
                            addHtml += "<td class='width-xs center'>~" + val.expiryDate + "</td>";
                            addHtml += "<td class='width-xs center'><button type='button' class='btn btn-white btn-xs js-excel-request-download' data-sno='" + val.sno + "' data-key='" + i + "' data-title='" + val.title +"'>다운로드</button></td>";
                        } else {
                            addHtml += "<td class='width-xs center'>생성중</td>";
                            addHtml += "<td class='width-xs center'>" + val.managerNm + "(" + val.managerId + ")</td>";
                            addHtml += "<td class='width-xs center'></td>";
                            addHtml += "<td class='width-xs center'><button type='button' class='btn btn-white btn-xs js-excel-request-download'>다운로드</button></td>";
                        }
                        addHtml += '</tr>';
                        pageNum++;
                    }
                });
            } else {
                addHtml += "<tr><td class='center no-data' colspan='8'>다운로드할 발주서 양식을 선택 후 요청 버튼을 눌러주세요.</td></tr>";
            }

            $("#tblExcelRequest tbody").html(addHtml);
            if (pageNum > 5) $("#tblExcelRequest thead tr th:last").show();
            else $("#tblExcelRequest thead tr th:last").hide();

            $('.js-excel-request-download').on('click', function () {

                // 다운로드 보안 설정 확인
                var authUseFl = false;
                $.ajax({
                    url: '../share/layer_excel_ps.php',
                    type: 'post',
                    async: false,
                    data: {'mode':'checkAuthUseFl', 'sno': $(this).data('sno')},
                    success: function (useFl) {
                        if (useFl == 'true') {
                            authUseFl = true
                        }
                    }
                });

                if ($(this).data('sno')) {
                    if (authUseFl) {
                        $("input[name='sno']").val($(this).data('sno'));
                        $("input[name='excelKey']").val($(this).data('key'));
                        $("input[name='excelTitle']").val($(this).data('title'));

                        $("#frmExcelRequest").submit();
                    } else {
                        var complied = _.template($('#downloadReason').html());
                        var message = complied();
                        var target = $(this);
                        BootstrapDialog.show({
                            title: '엑셀 다운로드 사유',
                            size: BootstrapDialog.SIZE_WIDE,
                            message: message,
                            buttons: [{
                                label: '확인',
                                cssClass: 'btn-black',
                                hotkey: 32,
                                size: BootstrapDialog.SIZE_LARGE,
                                action: function (dialog) {
                                    if ($('#excelDownloadReason').val() == '') {
                                        $('#reasonError').removeClass('display-none');
                                        return false;
                                    }
                                    dialog.close();
                                    $("input[name='sno']").val(target.data('sno'));
                                    $("input[name='excelKey']").val(target.data('key'));
                                    $("input[name='excelTitle']").val(target.data('title'));
                                    $("input[name='excelDownloadReason']").val($('#excelDownloadReason').val());
                                    $("#frmExcelRequest").submit();
                                }
                            }]
                        });
                    }
                    return false;
                } else {
                    alert("파일이 생성되지 않았습니다.");
                    return false;
                }

            });

            if (getData !== '') {
                $("#tblExcelRequest tbody").css("display", "block");
                $("#tblExcelRequest thead").css("display", "block");
                $("#tblExcelRequest tbody").css("height", "200px");
                $("#tblExcelRequest tbody").css("overflow-y", "auto");
                $("#tblExcelRequest tbody").css("overflow-x", "hidden");
            }

            $(".js-progress-excel").hide();
            $("#progressView").text("0%");
            $("#progressViewBg").css("width", "0%");
        });

    }

    function select_form(location) {

        $.post('../share/layer_excel_ps.php', {'mode': 'search_form', 'menu': '<?=$menu?>', 'location': location}, function (data) {

            var formList = $.parseJSON(data);
            var addHtml = "<option>선택</option>";
            if (formList) {
                $.each(formList, function (key, val) {
                    addHtml += "<option value='" + val.sno + "'>" + val.title + "</option>";
                });
            }
            $('select[name="formSno"]').html(addHtml);

        });

    }

    function progressExcel(size) {

        if ($.isNumeric(size) == false) {
            size = "100";
        }

        $("#progressView").text(size + "%");
        $("#progressViewBg").css({
            "background-color": "#fa2828",
            "width": size + "%"
        });
    }

    function cancelProgressExcel() {

        dialog_confirm('요청 취소 시 생성 중인 엑셀 다운로드 파일이 모두 삭제됩니다. 진행중인 내용을 취소하고 페이지를 이동하시겠습니까?', function (result) {
            if (result) {

                if ($.browser.msie) {
                    document.execCommand("Stop");
                } else {
                    window.stop(); //works in all browsers but IE
                }

                setTimeout(function () {
                    $(".js-progress-excel").hide();
                    $("#progressView").text("0%");
                    $("#progressViewBg").css("width", "0%");
                }, 10);

            } else {
                return false;
            }

        }, '확인', {"cancelLabel": '취소', "confirmLabel": '확인'});

    }

    function hide_process() {
        $(".js-progress-excel").hide();
    }

    function open_excel_auth() {
        var params = {
            mode: 'download',
            url: '../share/layer_excel_ps.php',
            data: $("#frmExcelRequest").serializeArray(),
            managerId: '<?=$managerId?>'
        };
        $.get('../share/layer_excel_auth.php', params, function (data) {
            console.log('layer_excel_auth', data);
            BootstrapDialog.show({
                title: '엑셀 다운로드 보안 인증',
                message: $(data),
                closable: false,
                onshow: function (dialog) {
                    var $modal = dialog.$modal;
                    BootstrapDialog.currentId = $modal.attr('id');
                }
            });
        });
    }

    //-->
</script>
<script type="text/html" id="downloadReason">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
            <tr style="border-top: 1px solid #E6E6E6;">
                <th>사유 선택</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box('excelDownloadReason', 'excelDownloadReason', $reasonList, null, null, '=사유 선택=', null, 'form-control'); ?>
                        <div id="reasonError" class="text-red display-none">사유 선택은 필수입니다.</div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="well">
        <div class="notice-info">개인정보의 안전성 확보조치 기준(고시)에 의거하여 개인정보를 다운로드한 경우 사유 확인이 필요합니다.</div>
    </div>
</script>
<script type="text/javascript">
    <!--
    <?php if (empty($tooltipData) === false) {?>
    $(document).ready(function () {
        // 툴팁 데이터
        var tooltipData = <?php echo $tooltipData;?>;
        var sectionEle = null;
        $('#frmExcelForm .table.table-cols th').each(function(idx){
            if ($(this).closest('table').siblings('.table-title').length) {
                sectionEle = $(this).closest('table').prevAll('.table-title:first');
            } else if ($(this).closest('table').parent('div').siblings('.table-title').length) {
                sectionEle = $(this).closest('table').parent('div').prevAll('.table-title:first');
            } else {
                sectionEle = $(this).closest('table').parent('div').parent('div').prevAll('.table-title:first');
            }
            if (typeof sectionEle[0] !== "undefined") {
                var sectionTitle = $(sectionEle[0]).html().replace(/\(?<\/?[^*]+>/gi, '').trim().replace(/ /gi, '').replace(/\n/gi, '');
                var titleName = $(this).text().trim().replace(/ /gi, '').replace(/\n/gi, '');
                for (var i in tooltipData) {
                    if (tooltipData[i].title == sectionTitle) {
                        if (tooltipData[i].attribute == titleName) {
                            $(this).append('<button type="button" onclick="tooltip(this)" class="btn btn-xs js-layer-tooltip" title="' + tooltipData[i].content + '" data-placement="right" data-width="' + tooltipData[i].cntWidth + '"><span title="" class="icon-tooltip"></span></button>');
                        }
                    }
                }
            }
        });
        $(document).on('click', '.tooltip.in .tooltip-close', function () {
            $('.js-layer-tooltip[aria-describedby=' + $(this).parent().attr('id') + ']').trigger('click');
        });
        $('button.close').click(function(){
            $('.tooltip.in .tooltip-close').trigger('click');
        });
    });

    function tooltip(e) {
        if ($(e).attr('aria-describedby')) {
            $(e).tooltip('destroy');
        } else {
            var option = {
                trigger: 'click',
                container: '#content',
                html: true,
                template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div><button class="tooltip-close">close</button></div>',
            };
            $(e).on('shown.bs.tooltip', function () {
                $(".tooltip.in").css({
                    width: 270,
                    maxWidth: "none",
                });
            });
            $(e).tooltip(option).tooltip('show');
        }
    }
    <?php }?>
    //-->

    $(document).ready(function(){
        $(document).on('click', '#btnAddOrderDraft', function() {
            win = popup({
                url: '../share/order_draft_excel_register.php',
                target: 'orderDraftPopup',
                width: 950,
                height: 800,
                scrollbars: 'yes',
                resizable: 'no'
            });
            win.focus();
            return win;
        });

        $(document).on('click', '#btnManageOrderDraft', function() {
            win = popup({
                url: '../share/order_draft_excel_list.php',
                target: 'orderDraftListPopup',
                width: 800,
                height: 700,
                scrollbars: 'yes',
                resizable: 'no'
            });
            win.focus();
            return win;
        });
    })

</script>
<style>
    .js-layer-tooltip {background-color: transparent;}
</style>
<!-- //@formatter:on -->
