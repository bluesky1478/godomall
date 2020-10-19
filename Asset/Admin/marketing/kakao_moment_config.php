<form id="frmConfig" action="dburl_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="type" value="config" />
    <input type="hidden" name="company" value="kakaoMoment" />
    <input type="hidden" name="mode" value="config" />

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small></small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red btn-save-config">
    </div>

    <div class="table-title">
        카카오모먼트 설정
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>카카오모먼트<br />사용설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="kakaoMomentFl" value="y" <?php echo gd_isset($checked['kakaoMomentFl']['y']); ?>/>사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="kakaoMomentFl" value="n" <?php echo gd_isset($checked['kakaoMomentFl']['n']); ?>/>사용안함
                </label>
                <div class="notice-info">
                    서비스를 사용하시려면, <a href="../marketing/marketing_info.php?menu=criteo_info" class="snote btn-link" target="_blank">리타게팅 광고 안내</a>에서 사용 신청 후 사용이 가능합니다.
                </div>
                <div class="notice-info">
                    '사용함' 설정 시 카카오모먼트 픽셀 스크립트가 활성화됩니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>서비스 적용범위</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="kakaoMomentRange" value="all" <?php echo gd_isset($checked['kakaoMomentRange']['all']); ?> <?php echo gd_isset($disabled); ?>/>PC + 모바일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="kakaoMomentRange" value="pc" <?php echo gd_isset($checked['kakaoMomentRange']['pc']); ?> <?php echo gd_isset($disabled); ?>/>PC
                </label>
                <label class="radio-inline">
                    <input type="radio" name="kakaoMomentRange" value="mobile" <?php echo gd_isset($checked['kakaoMomentRange']['mobile']); ?> <?php echo gd_isset($disabled); ?>/>모바일
                </label>
            </td>
        </tr>
        <tr>
            <th>고유코드(Track ID)</th>
            <td>
                <input type="text" name="kakaoMomentCode" maxlength="20" class="form-control js-number-only" style="width:250px;" value="<?php echo gd_isset($data['kakaoMomentCode']); ?>" <?php echo gd_isset($disabled); ?>/>
                <div class="notice-info" >
                    카카오모먼트에서 제공하는 고유코드(Track ID)를 정확히 입력하여 주시기 바랍니다.
                </div>
            </td>
        </tr>
    </table>
</form>
<div class="notice-info">
    별도로 카카오모먼트 픽셀 스크립트를 설치한 경우, 데이터가 중복으로 집계 될 수 있습니다.
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 미사용시 범위 및 코드 disabled 처리
        $('input[name="kakaoMomentFl"]').on('click', function () {
            if ($(this).val() === 'n') {
                $('input[name="kakaoMomentRange"]').attr('disabled', 'disabled');
                $('input[name="kakaoMomentCode"]').attr('disabled', 'disabled');
            } else {
                $('input[name="kakaoMomentRange"]').removeAttr('disabled');
                $('input[name="kakaoMomentCode"]').removeAttr('disabled');
            }
        });
        $(document).on('click','.btn-save-config', function (e) {
            if($('input[name=kakaoMomentFl]:checked').val() == "y" && $('input[name=kakaoMomentCode]').val() == "") {
                alert("고유코드(Track ID)를 입력해 주세요.");
                return false;
            }
        });
    });
    //-->
</script>
