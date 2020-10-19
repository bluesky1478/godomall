<div class="container-fluid">
    <form name="formCounsel" id="formCounsel">
        <input type="hidden" name="memNo" value="<?= $memberData['memNo']; ?>"/>
        <input type="hidden" name="sno" value="<?= $counselData['sno']; ?>"/>

        <div class="row">
            <div class="col-xs-12">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-xs">
                        <col>
                        <col class="width-xs">
                        <col>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th>등록자</th>
                        <td><?= $managerData['managerNm'] . '(' . $managerData['managerId'] . ')'; ?></td>
                        <th>상담회원</th>
                        <td><?= $memberData['memNm'] . '(' . $memberData['memId'] . ')'; ?></td>
                    </tr>
                    <tr>
                        <th>
                            상담수단
                        </th>
                        <td>
                            <select name="method" id="counselMethod" class="form-control">
                                <option value="p" <?php if($counselData['method'] == 'p') echo ' selected'; ?>>전화</option>
                                <option value="m" <?php if($counselData['method'] == 'm') echo ' selected'; ?>>메일</option>
                            </select>
                        </td>
                        <td>상담구분</td>
                        <td>
                            <select name="kind" id="counselType" class="form-control">
                                <option value="o" <?php if($counselData['kind'] == 'o') echo ' selected'; ?>>주문</option>
                                <option value="d" <?php if($counselData['kind'] == 'd') echo ' selected'; ?>>배송</option>
                                <option value="c" <?php if($counselData['kind'] == 'c') echo ' selected'; ?>>취소환불</option>
                                <option value="e" <?php if($counselData['kind'] == 'e') echo ' selected'; ?>>오류</option>
                                <option value="etc" <?php if($counselData['kind'] == 'etc') echo ' selected'; ?>>기타</option>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">상담내용</h3>
                    </div>
                    <div class="panel-body">
                        <textarea name="contents" class="form-control" rows="10" maxlength="1000"><?=str_replace(['\r\n', '\n'], chr(10), gd_htmlspecialchars_stripslashes($counselData['contents']));?></textarea>
                    </div>
                    <div class="panel-footer center">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-red btn-register">
                                <?php
                                if($counselData['sno'] > 0) {
                                    ?>
                                    수정
                                    <?php
                                } else {
                                    ?>
                                    등록
                                    <?php
                                }
                                ?>
                            </button>
                            <button type="button" class="btn btn-gray btn-cancel">취소</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $('#formCounsel').validate({
        submitHandler: function (form) {
            var params = $(form).serializeArray();
            if($('input[name=sno]').val()) {
                var writeMode = 'update';
                params.push({name: "mode", value: "update"});
            } else {
                var writeMode = 'register';
                params.push({name: "mode", value: "register"});
            }
            params.push({name: "memNo", value: $('input[name=memNo]').val()});

            $.post('../share/member_crm_counsel_ps.php', params, function (data) {
                opener.location.reload();
                dialog_alert(data, '알림',setTimeout(function(){
                    location.reload(true);
                    if(writeMode == 'update') {
                        self.close();
                    }
                }, 2000));
            });
        }
    });

    $('.btn-cancel').click(function () {
        self.close();
    });
</script>
