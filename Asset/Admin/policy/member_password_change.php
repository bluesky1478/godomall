<?php
/**
 * @todo: 비밀번호 안내주기에따른 자동메일 발송 개발
 */
?>
<form id="frmSetup" action="../policy/member_ps.php" method="post">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?>
            <small></small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        비밀번호 변경안내 설정
    </div>
    <div class="form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col class=""/>
            </colgroup>
            <tr>
                <th>관리자 사용설정</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="managerFl" value="y" <?= $checked['managerFl']['y'] ?>>
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="managerFl" value="n" <?= $checked['managerFl']['n'] ?>>
                        사용안함
                    </label>
                    <p class="notice-info">장기간 비밀번호를 변경하지 않은 관리자가 관리자 화면 로그인 시 비밀번호 변경을 안내합니다.</p>
                </td>
            </tr>
            <tr>
                <th>쇼핑몰 사용설정</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="memberFl" value="y" <?= $checked['memberFl']['y'] ?>>
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="memberFl" value="n" <?= $checked['memberFl']['n'] ?>>
                        사용안함
                    </label>
                    <div class="notice-info">장기간 비밀번호를 변경하지 않은 회원이 쇼핑몰 화면 로그인 시 비밀번호 변경을 안내합니다.</div>
                </td>
            </tr>
            <tr>
                <th>비밀번호 변경<br/>안내 주기</th>
                <td>
                    비밀번호 최종 변경일 기준
                    <?php
                    $guidePeriodHtml = [];
                    $guidePeriodHtml[] = '<select name="guidePeriod" id="guidePeriod" class="form-control">';
                    $guideMax = ($selected['guidePeriodItem']['month']) ? 12 : 100;
                    for ($i = 1; $i <= $guideMax; $i++) {
                        if ($data['guidePeriod'] == $i) {
                            $guidePeriodHtml[] = '<option value="' . $i . '" selected="selected">' . $i . '</option>';
                        } else {
                            $guidePeriodHtml[] = '<option value="' . $i . '">' . $i . '</option>';
                        }

                    }
                    $guidePeriodHtml[] = '</select>';
                    echo join('', $guidePeriodHtml);
                    ?>
                    <select name="guidePeriodItem" id="guidePeriodItem" class="form-control"
                            data-target-name="guidePeriod" data-target-value="<?= $data['guidePeriod'] ?>">
                        <option data-target-number="2, 12, 6"
                                value="month" <?= $selected['guidePeriodItem']['month'] ?>>개월
                        </option>
                        <option data-target-number="3, 100, 1" value="day" <?= $selected['guidePeriodItem']['day'] ?>>
                            일
                        </option>
                    </select> 마다 로그인 시 안내
                </td>
            </tr>
            <tr>
                <th>비밀번호 변경<br/>재안내 주기</th>
                <td>
                    <?php
                    $reGuidePeriodHtml = [];
                    $reGuidePeriodHtml[] = '<select name="reGuidePeriod" id="reGuidePeriod" class="form-control">';
                    $reGuideMax = ($selected['reGuidePeriodItem']['month']) ? 12 : 100;
                    for ($i = 1; $i <= $reGuideMax; $i++) {
                        if ($data['reGuidePeriod'] == $i) {
                            $reGuidePeriodHtml[] = '<option value="' . $i . '" selected="selected">' . $i . '</option>';
                        } else {
                            $reGuidePeriodHtml[] = '<option value="' . $i . '">' . $i . '</option>';
                        }

                    }
                    $reGuidePeriodHtml[] = '</select>';
                    echo join('', $reGuidePeriodHtml);
                    ?>
                    <select name="reGuidePeriodItem" id="reGuidePeriodItem" class="form-control"
                            data-target-name="reGuidePeriod" data-target-value="<?= $data['reGuidePeriod'] ?>">
                        <option data-target-number="2, 12, 1"
                                value="month" <?= $selected['reGuidePeriodItem']['month'] ?>>개월
                        </option>
                        <option data-target-number="3, 100, 1" value="day" <?= $selected['reGuidePeriodItem']['day'] ?>>
                            일
                        </option>
                    </select> 마다 로그인 시 재안내
                    <div class="notice-info">비밀번호 변경안내 화면에서 [다음에 변경하기] 선택 시 재안내할 기간을 설정합니다.</div>
                </td>
            </tr>
        </table>
    </div>

<!--    <div class="panel panel-default">-->
<!--        <div class="panel-heading">유의사항 안내</div>-->
<!--        <div class="panel-body" style="height: 150px;padding: 10px;">-->
<!--            방송통신위원회 고시 [개인정보의 기술적 ㆍ관리적 보호조치 기준]에 따라, 정보통신서비스 제공자등은 개인정보 취급자를 대상으로<br/><br/> 비밀번호에 유효기간을 설정하여 6개월에 1회 이상 변경하여야 합니다.<br/> [ 디자인관리 > 디자인페이지 > 회원 > 회원비밀번호 변경안내 ] 페이지 에서 비밀번호 변경안내 문구 및 디자인 수정이 가능합니다.<br/>-->
<!--            <strong class="notice-ref notice-sm"><a href="#">> 도움말 자세히 보기</a></strong>-->
<!--        </div>-->
<!--    </div>-->
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        var $frmSetup = $('#frmSetup');
        $frmSetup.validate({
            submitHandler: function (form) {
                var data = $(form).serializeArray();
                data.push({name: 'mode', value: 'password_change'});
                post_with_reload('../policy/member_ps.php', data);
            }
        });

        $('#guidePeriodItem', $frmSetup).on('change', function (e) {
            var $target = $(e.target);
            var $dataTarget = $('select[name="' + $target.data('target-name') + '"]');
            var optionHtml = [];
            var max = 101;
            if ($target.val() == 'month') {
                max = 13;
            }
            for (var i = 1; i < max; i++) {
                optionHtml[i] = '<option value="' + i + '">' + i + '</option>';
            }
            $dataTarget.html(optionHtml.join());
        });

        $('#reGuidePeriodItem', $frmSetup).on('change', function (e) {
            var $target = $(e.target);
            var $dataTarget = $('select[name="' + $target.data('target-name') + '"]');
            var optionHtml = [];
            var max = 101;
            if ($target.val() == 'month') {
                max = 13;
            }
            for (var i = 1; i < max; i++) {
                optionHtml[i] = '<option value="' + i + '">' + i + '</option>';
            }
            $dataTarget.html(optionHtml.join());
        });
    });
    //-->
</script>
