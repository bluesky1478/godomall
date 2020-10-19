<form name="frmSetFilePassword" id="frmSetFilePassword" method="post" action="<?=$data['action'];?>">
    <input type="hidden" name="mode" value="<?=$data['mode'];?>"/>
    <div>
        <?php if (gd_is_plus_shop(PLUSSHOP_CODE_REVIEW)) { ?>
        <div class="table-title">
            리뷰 파일 다운로드 범위 설정
        </div>
        <div class="mgb10">플러스리뷰를 설치한 쇼핑몰은 원하는 리뷰 파일을 선택하여 다운로드 가능합니다.<br/>다운로드 원하는 리뷰 파일을 선택해주세요.</div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>다운로드 범위</th>
                <td colspan="3">
                    <div class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="reviewCsvRange" value="all" checked="checked"/> 전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="reviewCsvRange" value="review" /> 일반 리뷰
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="reviewCsvRange" value="plusReview" /> 플러스 리뷰
                        </label>
                    </div>
                </td>
            </tr>
        </table>
        <?php } ?>
        <div class="table-title">
            파일 비밀번호 설정
        </div>
        <div class="mgb10 text-red">개인정보를 개인용 PC에 저장할 시 암호화가 의무이므로 비밀번호 설정은 필수 입니다.</div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>비밀번호 설정</th>
                <td colspan="3">
                    <div class="form-inline"><input type="password" name="password"  class="form-control width-xl js-type-korea js-maxlength" maxlength="50"></div>
                </td>
            </tr>
            <tr>
                <th>비밀번호 확인</th>
                <td colspan="3">
                    <div class="form-inline"><input type="password" name="checkPassword"  class="form-control width-xl js-type-korea js-maxlength" maxlength="50"></div>
                </td>
            </tr>
        </table>
    </div>
    <div class="table-btn">
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
        <button type="submit" class="btn btn-lg btn-black js-layer-submit">설정완료</button>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function() {
        // 폼체크
        $('#frmSetFilePassword').validate({
            dialog: false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
                $(".js-progress-file").show();
            },
            rules: {
                reviewCsvRange: {
                    required: function () {
                        return $(':radio[name="reviewCsvRange"]').length > 0;
                    }
                },
                password : {
                    required: true
                },
                checkPassword : {
                    required: true,
                    equalTo: 'input[name=password]'
                }
            },
            messages: {
                reviewCsvRange : {
                    required: '다운로드 범위를 선택해주세요.'
                },
                password : {
                    required: '비밀번호를 입력해주세요.'
                },
                checkPassword : {
                    required: '비밀번호를 입력해주세요.',
                    equalTo: '동일한 비밀번호를 입력해주세요.'
                }
            }
        });

        // 툴팁 데이터
        var tooltipData = <?php echo $tooltipData;?>;
        $('#frmSetFilePassword .table.table-cols th').each(function(idx){
            var titleName = $(this).text().trim().replace(/ /gi, '').replace(/\n/gi, '');
            for (var i in tooltipData) {
                if (tooltipData[i].attribute == titleName) {
                    $(this).append('<button type="button" class="btn btn-xs js-layer-tooltip" title="' + tooltipData[i].content + '" data-placement="right" data-width="' + tooltipData[i].cntWidth + '" onclick="tooltip(this)"><span title="" class="icon-tooltip"></span></button>');
                }
            }
        });

        // 툴팁 제거 이벤트
        $(document).on('click', '.tooltip.in .tooltip-close', function () {
            $('.js-layer-tooltip[aria-describedby=' + $(this).parent().attr('id') + ']').trigger('click');
        });

        // 레이어 생성/제거시에 툴팁 제거
        $(document).on('shown.bs.modal hidden.bs.modal', '.modal', function () {
            if ($('.tooltip.in').length) {
                $('.tooltip.in').tooltip('destroy');
            }
        });

        // progress 컴파일
        var complied = _.template($('#progressFile').html());
        $(".modal-content").append(complied());
    });

    // 툴팁 레이어
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
                    width: $(this).data('width'),
                    maxWidth: "none",
                });
            });
            $(e).tooltip(option).tooltip('show');
        }
    }

    // 파일 생성 progress 노출
    function progressFile(title, size) {
        if (!_.isUndefined(title)) {
            $('#csvTitle').text(title);
        }

        if ($.isNumeric(size) == false) {
            size = "100";
        }

        $("#progressView").text(size + "%");
        $("#progressViewBg").css({
            "background-color": "#fa2828",
            "width": size + "%"
        });
    }

    // 파일 생성 취소
    function cancelProgressFile() {
        dialog_confirm('진행중인 내용을 취소하고 페이지를 이동하시겠습니까?', function (result) {
            if (result) {

                if ($.browser.msie) {
                    document.execCommand("Stop");
                } else {
                    window.stop(); //works in all browsers but IE
                }

                setTimeout(function () {
                    $(".js-progress-file").hide();
                    $("#progressView").text("0%");
                    $("#progressViewBg").css("width", "0%");
                }, 10);

            } else {
                return false;
            }
        }, '확인', {"cancelLabel": '취소', "confirmLabel": '확인'});
    }

    // progress 숨기기
    function hide_process(msg) {
        if (msg) {
            alert(msg);
        }
        $(".js-progress-file").hide();
    }

    // csv 생성 후 다운로드
    function complete_create_csv(msg) {
        $(".js-progress-excel").hide();
        $("#progressView").text("0%");
        $("#progressViewBg").css("width", "0%");

        var dialog = BootstrapDialog.dialogs[BootstrapDialog.currentId];
        if (dialog) {
            dialog.close();
        }

        BootstrapDialog.show({
            title: '파일 다운로드',
            message: msg,
            type: BootstrapDialog.TYPE_INFO,
            closable: false,
            buttons: [
                {
                    id: 'btn-down',
                    label: '확인',
                    cssClass: 'btn-black',
                    action: function(dialog) {
                        var $downButton = this;
                        $downButton.disable();
                        $('#btn-down').html('CSV 파일 다운로드중입니다.');
                        $downButton.spin();
                        location.href = 'crema_ps.php?mode=download&excelDownloadReason=' + $("input[name='excelDownloadReason']").val();
                        setTimeout(function() {
                            $('#btn-down').hide();
                        }, 3000);
                        setTimeout(function() {
                            dialog.close();
                            location.reload();
                        }, 6000);
                    }
                }
            ]
        });
    }
    //-->
</script>
<script type="text/html" id="progressFile">
    <div class="js-progress-file" style="position:absolute;width:100%;height:100%;top:0;left:0;background:#000000;z-index:1060;opacity:0.5;display:none;"></div>
    <div class="js-progress-file" style="width:300px;background:#fff;margin:0 auto;position:absolute;z-index:1070;top:250px;padding:20px;left:270px;text-align:center;display:none;">크리마 간편리뷰 사용을 위해 <span id="csvTitle"></span> CSV 파일을 생성 중입니다.<br/> 잠시만 기다려주세요.
        <p style="font-size:22px;" id="progressView">0%</p>
        <div style="width:260px;height:18px;background:#ccc;margin-bottom:10px;">
            <div id="progressViewBg" style="height:100%">&nbsp;</div>
        </div>
        <a onclick="cancelProgressFile()" style="cursor:pointer">요청 취소</a>
    </div>
</script>