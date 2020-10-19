<form id="frmExcelMember" name="frmExcelMember" action="excel_member_ps.php" method="post" enctype="multipart/form-data">
    <div class="page-header js-affix">
        <h3>회원 엑셀 업로드</h3>
    </div>
    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location); ?>
    </div>

    <input type="hidden" name="mode" value="excel_up"/>
    <input type="hidden" name="preFix" value="Member_Result"/>

    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>엑셀파일 업로드</th>
                <td>
                    <div class="form-inline">
                        <input type="file" name="excel" value="" class="form-control"/>
                        <input type="button" class="btn btn-sm btn-white js-upload" value="엑셀업로드"/>
                    </div>
                    <div class="notice-danger mgt10">
                        엑셀업로드를 통해 신규 회원 등록 시 자동발송 설정에 따라 회원에게 SMS/메일로 안내메시지가 발송되므로 주의하시기 바랍니다.
                    </div>
                    <div class="notice-info">
                        엑셀업로드 전 회원가입 시 발송 가능한 항목(회원가입, 마일리지 지급, 회원가입 축하 쿠폰)의 발송설정을 확인해주시기 바랍니다. <a href="/member/sms_auto.php" target="_blank" class="btn-link">SMS설정&gt;</a>&nbsp;&nbsp;<a href="/member/mail_config_auto.php" target="_blank" class="btn-link">자동 메일 설정&gt;</a>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</form>

<div class="information">
    <div class="helper_left">
        <div class="helper_right">
            <div class="helper_bottom">
                <div class="helper_right_top">
                    <div class="helper_right_bottom">

                        <div class="content">
                            <ul class="pdl0">
                                <li>
                                    <h3>회원 엑셀 샘플 다운로드</h3>
                                    1. 아래 &quot;회원 엑셀 샘플 다운로드&quot; 버튼을 눌러 샘플을 참고하시기 바랍니다.<br/> 2. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;로 저장을 하셔야 합니다. 그외 csv 나 xlsx파일 등은 지원이 되지 않습니다.<br/>
                                    <form id="frmExcelGoods" name="frmExcelGoods" action="excel_member_ps.php" method="post">
                                        <input type="hidden" name="mode" value="excel_sample_down"/>
                                        <input type="hidden" name="preFix" value="Member_Sample"/>
                                        <input type="submit" value="회원 엑셀 샘플 다운로드" class="btn btn-white btn-icon-excel mgt10"/>
                                    </form>
                                </li>
                                <li>
                                    <h3>회원 업로드 방법</h3>
                                    1. 아래 항목 설명되어 있는 것을 기준으로 엑셀 파일을 작성을 합니다.<br/> 2. 회원 다운로드에서 받은 엑셀이나 &quot;회원 엑셀 샘플 다운로드&quot;에서 받은 엑셀을 기준으로 파일을 작성을 합니다.<br/> 3. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;로 저장을 하셔야 합니다. 절대 csv 파일이 아닌 진짜 엑셀 파일입니다.(xls)<br/> 4. 작성된 엑셀 파일을 업로드 합니다.<br/>
                                </li>
                                <li>
                                    <h3>주의사항</h3>
                                    1. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;만 가능하며, csv 파일은 업로드가 되지 않습니다.<br/> 2. 엑셀 2003 사용자의 경우는 그냥 저장을 하시면 되고, 엑셀 2007 이나 엑셀 2010 인 경우는 새이름으로 저장을 선택해서 &quot;Excel 97-2003 통합문서&quot;로 저장을 하십시요.<br/> 3. 엑셀의 내용이 너무 많은 경우 업로드가 불가능 할수 있으므로 100개나 200개 단위로 나누어 올리시기 바랍니다.<br/> 4. 엑셀 파일이 작성이 완료 되었다면 하나의 회원만 테스트로 올려보고 확인후 이상이 없으시면 나머지를 올리시기 바랍니다.<br/> 5. 엑셀 내용중 &quot;1번째 줄은 설명&quot;, &quot;2번째 줄은 excel DB 코드&quot;, &quot;3번째 줄은 설명&quot; 이며, &quot;4번째 줄부터&quot; 데이터 입니다.<br/> 6. 엑셀 내용중 2번째 줄 &quot;excel DB&quot; 코드는 필수 데이타 입니다. 그리고 반드시 내용은 &quot;4번째 줄부터&quot; 작성 하셔야 합니다.<br/>7. 엑셀샘플파일 내 일부 열을 삭제하고 업로드하면 회원정보 등록 및 수정이 불가능하니 유의 바랍니다.<br/>
                                </li>
                                <li>
                                    <h3>항목 설명</h3>
                                    1. 아래 설명된 내용을 확인후 작성을 해주세요.<br/>
                                </li>
                            </ul>
                            <table class="input">
                                <colgroup>
                                    <col class="width-sm"/>
                                    <col/>
                                </colgroup>
                                <tr>
                                    <th>항목 설명</th>
                                    <td>
                                        <table class="content_table">
                                            <colgroup>
                                                <col class="width-sm"/>
                                                <col class="width-xs"/>
                                                <col/>
                                            </colgroup>
                                            <thead>
                                            <tr>
                                                <th>한글필드명</th>
                                                <th>영문필드명</th>
                                                <th>설명</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            foreach ($excelField as $key => $val) {
                                                ?>
                                                <tr>
                                                    <th class="desc01">
                                                    <?php echo $val['text']; ?></td>
                                                    <td class="desc02"><?php echo $val['excelKey']; ?></td>
                                                    <td><?php echo $val['desc']; ?></td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $(".js-upload").click(function(){
            var data = {
                'mode': 'checkSendSms',
                'checkType': 'join'
            }
            var $ajax = $.ajax('../member/member_ps.php', {type: "post", data: data});
            $ajax.done(function (data, textStatus, jqXHR) {
                if(data === true) {
                    $.get('../share/layer_sms_password.php?mode=input', function () {
                        BootstrapDialog.show({
                            title: 'SMS 인증번호 입력',
                            size: BootstrapDialog.SIZE_WIDE,
                            message: arguments[0]
                        });
                    });
                } else {
                    $('#frmExcelMember').submit();
                }
            });
        });
    });
    function sms_password_callback() {
        $('#frmExcelMember').submit();
    }
</script>