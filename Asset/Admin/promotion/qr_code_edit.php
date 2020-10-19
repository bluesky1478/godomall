<form id="frm" action="../promotion/qr_code_ps.php" method="post">
    <input type="hidden" name="sno" value="<?php echo $data['sno']; ?>"/>
    <?php
    if (gd_isset($data['sno'], '') === '') {
        echo '<input type="hidden" name="mode" value="save"/>';
    } else {
        echo '<input type="hidden" name="mode" value="edit"/>';
    }
    ?>

    <input type="hidden" name="qrType" value="etc"/>
    <input type="hidden" name="qrString" value=""/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./qr_code_list.php');" />
            <input type="button" value="미리보기" class="preview btn btn-white"/>
            <a href="#" class="download btn btn-white">PC저장</a>
            <input type="button" value="저장" class="save btn btn-red"/>
        </div>
    </div>

    <div class="table-title">QR코드 설정</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>QR코드 타입</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="useType" value="url" <?php echo ($data['useType'] == 'url') ? 'checked' : ''; ?> />
                    URL/TEXT
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useType" value="MECARD" <?php echo ($data['useType'] == 'MECARD') ? 'checked' : ''; ?> />
                    명함(연락처)
                </label>
            </td>
        </tr>
        <tr>
            <th class="require">QR코드명</th>
            <td>
                <span title="이름을 입력해주세요!">
                    <input type="text" name="qrName" class="form-control width-sm" value="<?php echo $data['qrName']; ?>"/>
                </span>
                <span id="qrNameMsg" class="input_error_msg"></span>
            </td>
        </tr>
        <tr>
            <th>QR코드 크기</th>
            <td>
                <?php echo $data['qrSizeHtml']; ?>
                <p class="notice-info">1 (90pix) ~ 8 (405pix) : 1레벨 당 45pix 증가</p>
            </td>
        </tr>
        <tr>
            <th>QR코드 정밀도</th>
            <td><?php echo $data['qrVersionHtml']; ?>
                <p class="notice-info">내용이 많을 경우 정밀도를 올려주세요.(코드가 커질수도 있습니다.)</p>
            </td>
        </tr>
    </table>

    <div class="table-title">QR코드 내용</div>
    <table class="table table-cols <?php echo ($data['useType'] == 'MECARD') ? '' : 'display-none'; ?>" id="nameCardTable">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th class="">이름</th>
            <td>
                <input type="text" class="form-control" name="N" value="<?php echo $data['N']; ?>"/>
                ex) 대표 {rc_memNm}(고도몰)
            </td>
        </tr>
        <tr>
            <th class="">전화번호</th>
            <td>
                <input type="text" class="form-control" name="TEL" value="<?php echo $data['TEL']; ?>"/>
            </td>
        </tr>
        <tr>
            <th class="">이메일</th>
            <td>
                <input type="text" class="form-control" name="EMAIL" value="<?php echo $data['EMAIL']; ?>"/>
            </td>
        </tr>
        <tr>
            <th class="">홈페이지</th>
            <td>
                <input type="text" class="form-control" name="URL" value="<?php echo $data['URL']; ?>"/>
            </td>
        </tr>
        <tr>
            <th class="">주소</th>
            <td>
                <input type="text" class="form-control" name="ADR" value="<?php echo $data['ADR']; ?>"/>
            </td>
        </tr>
        <tr>
            <th>미리보기</th>
            <td><img src="<?php echo $data['qrCodeFilePath']; ?>" class="previewQrCode"/></td>
        </tr>
        </tbody>
    </table>
    <table class="table table-cols <?php echo ($data['useType'] == 'url') ? '' : 'display-none'; ?>" id="urlTable">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th class="require">내용(URL)</th>
            <td>
                <textarea name="contentText" rows="5" class="form-control"><?php echo $data['contentText']; ?></textarea>
            </td>
        </tr>
        <tr>
            <th>미리보기</th>
            <td>
                <img src="" class="previewQrCode"/>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    var qrCode = (function ($) {
        var form = $('#frm');
        var validate;
        var validate_options = {
            defaults: {
                ignore: ".ignore",
                rules: {
                    qrName: "required"
                }, messages: {
                    qrName: "QR코드명을 입력해 주세요."
                }
            }, name_card: {
                rules: {
                    N: "required",
                    TEL: "required"
                }, messages: {
                    N: "명함(연락처)의 이름을 입력하세요.",
                    TEL: "전화번호를 입력하여 주십시오."
                }
            }, url: {
                rules: {
                    contentText: "required"
                }, messages: {
                    contentText: "QR코드 내용을 입력해 주세요."
                }
            }
        };

        /**
         * QR코드타입에 맞는 검증 규칙을 설정
         * @param settings
         */
        function set_validate_settings(settings) {
            delete settings.rules;
            delete settings.messages;

            if ($(':radio[name=useType]:eq(1)').prop('checked')) {
                $.extend(true, settings, validate_options.defaults, validate_options.name_card);
            } else {
                $.extend(true, settings, validate_options.defaults, validate_options.url);
            }
        }

        /**
         *  QR코드 생성할 문구 조합
         */
        function set_qr_string() {
            if ($(':radio[name=useType]:eq(1)').prop('checked')) {
                $('input[name=qrString]').
                    val("MECARD:N:" + $('input[name=N]').val() + ";TEL:" + $('input[name=TEL]').val() + ";EMAIL:" + $("input[name=EMAIL]").val() + ";URL:" + $("input[name=URL]").val() + ";ADR:" + $("input[name=ADR]").val());
            } else {
                $('input[name=qrString]').val($('textarea[name=contentText]').val());
            }
        }

        /**
         * 검증 및 요청 실행
         * @param func submitHandler 함수
         */
        function validation_submit(func) {
            set_qr_string();

            validate = form.validate();

            set_validate_settings(validate.settings);

            validate.settings.submitHandler = func;

            if (validate.form()) {
                form.submit();
            }
        }

        return {
            /**
             * 저장
             */
            save: function () {
                validation_submit(function (form) {
                    post_with_reload('../promotion/qr_code_ps.php', $(form).serializeArray());
                });
            },
            /**
             * 미리보기
             */
            preview: function (e) {
                validation_submit(function (form) {
                    var params = $(form).serializeArray();
                    params.push({name: "mode", value: "preview"});
                    ajax_with_layer('../promotion/qr_code_ps.php', params, function (data) {
                        layer_close();
                        $('.previewQrCode').attr('src', data.previewImage);
                        if (e) {
                            alert(data.resultMessage);
                        }
                    });
                });
            },
            /**
             * QR코드 타입 라디오버튼 이벤트 함수
             * @param e
             */
            typeChange: function (e) {
                var $target = $(e.target);
                if ($target.val() === 'MECARD') {
                    $('#urlTable').addClass('display-none');
                    $('#nameCardTable').removeClass('display-none');
                } else {
                    $('#urlTable').removeClass('display-none');
                    $('#nameCardTable').addClass('display-none');
                }
                $('.previewQrCode').attr('src', '');
            }, download: function (e) {
                e.preventDefault();
                set_qr_string();
                validate = form.validate();
                set_validate_settings(validate.settings);
                if (validate.form()) {
                    var params = $('#frm').serialize();
                    location.href = '../promotion/qr_code_download.php?' + params;
                }
            }
        };
    })($);

    $(document).ready(function () {
        $('input.save').click(qrCode.save);
        $('input.preview').click(qrCode.preview);
        $('.download').click(qrCode.download);
        $(':radio[name=useType]').change(qrCode.typeChange);
        <?php
        if (gd_isset($data['sno'], '') !== '') {
            echo 'qrCode.preview();';
        }
        ?>
    });
</script>
