<form id="frmCreate" name="frmCreate" action="design_page_edit_ps.php" method="post">
    <input type="hidden" name="mode" value="designPageCreate"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>
    <input type="hidden" name="dirPath" value="<?php echo $dirPath; ?>"/>
    <input type="hidden" name="saveMode" value="<?php echo $saveMode; ?>"/>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>폴더명</th>
            <td><?php echo $dirText; ?></td>
        </tr>
        <tr>
            <th class="require">파일명</th>
            <td>
                <div class="form-inline">
                    <input type="hidden" name="chkFileName" value=""/>
                    <input type="text" name="fileName" value="" maxlength="100" class="form-control js-maxlength width-md" style="ime-mode:disabled;"/>
                    <select name="fileExt" class="form-control">
                        <option value=".html">.html</option>
                        <option value=".txt">.txt</option>
                    </select>
                    <button type="button" class="btn btn-black btn-hf js-overlap_fileNm">중복확인</button>
                </div>
            </td>
        </tr>
        <tr>
            <th>파일설명</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="fileText" value="" maxlength="30" class="form-control js-maxlength width-2xl" />
                </div>
            </td>
        </tr>
        <tr>
            <th>페이지 주소</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="fileUrl" value="[중복확인]클릭 시 생성되는 페이지 주소가 노출됩니다." class="form-control js-maxlength width-2xl" readonly="readonly" />
                </div>
            </td>
        </tr>
    </table>
    <textarea class="display-none" id="fileContent" name="fileContent"></textarea>
    <div class="pd15 text-center">
        <input type="submit" value="새로운 페이지 추가하기" class="btn btn-lg btn-black"/>
    </div>
</form>

<script type="text/javascript">
    <!--
    var dirPath = '<?php echo $dirPath;?>';
    var skinType = '<?php echo $skinType;?>';

    $(document).ready(function () {
        var fileContent = '';
        <?php if ($saveMode === 'create') { ?>
        var html = new Array();
        var n = -1;
        html[++n] = '{ # header }';
        html[++n] = '';
        html[++n] = '{ # footer }';
        fileContent = html.join("\n");
        <?php } else { ?>
        $('#frmCreate input[name=fileText]').val($('#frmDesign input[name=text]').val());
        <?php } ?>
        // 파일 내용
        $('#fileContent').val(fileContent);

        // 저장하기
        $("#frmCreate").validate({
            submitHandler: function (form) {
                <?php if ($saveMode === 'create') { ?>
                form.target = 'ifrmProcess';
                form.submit();
                <?php } else { ?>
                var designPage = $('#frmCreate input[name=dirPath]').val() + '/' + $('#frmCreate input[name=fileName]').val() + $('#frmCreate select[name=fileExt]').val();
                $('#frmDesign input[name=designPage]').val(designPage);
                $('#frmDesign input[name=text]').val($('#frmCreate input[name=fileText]').val());
                $('#frmDesign input[name=mode]').val('saveas');
                $('#frmDesign').target = 'ifrmProcess';
                $('#frmDesign').submit();
                <?php } ?>
            },
            dialog: false,
            rules: {
                fileName: {
                    required: true,
                    equalTo: 'input[name=chkFileName]',
                }
            },
            messages: {
                fileName: {
                    required: '파일 이름을 입력하세요.',
                    equalTo: '파일 이름 중복체크를 해주세요.',
                }
            }
        });

        // 파일이름 재입력 체크 이벤트
        $('#frmCreate input[name=fileName]').change(function () {
            $('#frmCreate input[name=chkFileName]').val('');
        });
        $('#frmCreate select[name=fileExt]').change(function () {
            $('#frmCreate input[name=chkFileName]').val('');
        });

        // 파일이름 중복확인 이벤트
        $('.js-overlap_fileNm').click(function () {
            if ($('#frmCreate input[name=fileName]').val() == '') {
                alert('파일이름을 입력하세요.');
                $('#frmCreate input[name=fileName]').focus();
                return;
            }
            if (!/^[ a-zA-Z0-9~!@#$%\^&()\-_+=\{\}\[\];',\.]+$/.test($('#frmCreate input[name=fileName]').val())) {
                alert('파일이름에 다음문자를 사용할 수 없습니다.\n한글 \ / : * ? " < > |');
                $('#frmCreate input[name=fileName]').focus();
                return;
            }

            var data = {
                'mode': 'overlapDesignFile',
                'skinType': skinType,
                'dirPath': dirPath,
                'fileName': $('#frmCreate input[name=fileName]').val() + $('#frmCreate select[name=fileExt]').val()
            };
            $.ajax({
                type: 'GET'
//                , url: '../design/common_ax.php'
                , url: 'common_ax.php'
                , data: data
                , dataType: 'text'
                , async: false
                , success: function (response) {
                    response = JSON.parse(response);
                    if (response['result'] == true) {
                        alert('사용이 가능합니다.');
                        $('#frmCreate input[name=chkFileName]').val($('#frmCreate input[name=fileName]').val());
                        $('#frmCreate input[name=fileUrl]').val(response['designUrl']['realLinkurl']);
                    } else {
                        alert('이미 사용중인 파일이름입니다.');
                        $('#frmCreate input[name=fileUrl]').val('[중복확인]클릭 시 생성되는 페이지 주소가 노출됩니다.');
                    }
                }
            });
        });
    });
    //-->
</script>
