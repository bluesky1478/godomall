<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <?php if($shopStatus != 'e') { ?>
    <div class="btn-group">
        <input type="button" value="저장" class="btn btn-red btn-modify">
    </div>
    <?php } ?>
</div>
<form name="acecounterForm" id="acecounterForm" method="post" action="acecounter_ps.php">
    <input type="hidden" name="mode" value="" />
    <input type="hidden" name="shopStatus" value="<?=$shopStatus?>" />
    <input type="hidden" name="aceStatus" value="<?=$aceStatus?>" />
    <div class="acecounterJoinDiv acecounterDiv">
        <h5 class="table-title">에이스카운터+ 약관동의 및 서비스 신청</h5>
        <div id="aceAgreement" class="form-inline panel pd10 overflow-y-scroll ace-agreement">
            <?=$terms?>
        </div>
        <div class="agree-div">
            <input type="checkbox" id="acecounterServiceAgree" name="acecounterServiceAgree" />
            <label for="acecounterServiceAgree">에이스카운터+ 서비스 신청 및 이용약관에 동의합니다.</label>
        </div>
        <h5 class="table-title">개인정보의 제3자 제공</h5>
        <div id="aceAgreement" class="form-inline panel pd10 overflow-y-scroll ace-agreement">
            <?=$privateTerms?>
        </div>
        <div class="agree-div">
            <input type="checkbox" id="acecounterPrivateAgree" name="acecounterPrivateAgree" />
            <label for="acecounterPrivateAgree">개인정보 제3자 제공에 동의합니다.</label>
        </div>
        <div class="ta-c"><button type="button" class="btn btn-lg btn-black btn-register">서비스 신청</button></div>
    </div>
    <div class="acecounterSettingDiv acecounterDiv">
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col/></colgroup>
            <tr>
                <th>사용 여부</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="aceCommonScriptFl" value="y" <?php echo gd_isset($checked['aceCommonScriptFl']['y']);?> />사용함</label>
                    <label class="radio-inline"><input type="radio" name="aceCommonScriptFl" value="n" <?php echo gd_isset($checked['aceCommonScriptFl']['n']);?> />사용안함</label>
                </td>
            </tr>
            <tr>
                <th>서비스 신청</th>
                <td>신청완료 - 사용가능</td>
            </tr>
            <tr>
                <th>사용중인 상품</th>
                <td>
                    <?=$sName?>
                    <div class="notice-info mgb10">
                        서비스 상품 변경은 에이스카운터 관리자에서 신청하시기 바랍니다. <a href="../statistics/acecounter_manager.php">바로가기 &gt;</a>
                    </div>
                </td>
            </tr>
            <tr>
                <th>사용 상태</th>
                <td>등록</td>
            </tr>
            <tr>
                <th>분석 사이트 관리</th>
                <td>
                    <button type="button" class="btn btn-sm btn-black btn-ace-link">분석 사이트 관리</button>
                    <div class="notice-info mgb10">대표/서브 도메인 변경 혹은 추가 시 분석 사이트 관리에서 추가해 주셔야 원활한 서비스 이용이 가능합니다.</div>
                    <div class="notice-info mgb10">해외몰 추가 시 기본 또는 도메인을 사용하실 경우 별도 url을 추가하지 않아도 데이터가 수집됩니다.</div>
                    <div class="notice-info mgb10">해외몰 연결도메인을 추가하신 경우 분석 사이트 관리에 해외몰 url을 추가해주셔야 정상적으로 데이터 수집이 가능합니다.</div>
                </td>
            </tr>
        </table>
    </div>
    <div>

    </div>
</form>
<form name="loginForm" id="loginForm" method="post">
    <input type="hidden" name="shopKey" value="<?=$directLogin['shopKey']?>" class="loginCheck" />
    <input type="hidden" name="hashData" value="<?=$directLogin['hashData']?>" class="loginCheck" />
    <input type="hidden" name="shopSno" value="<?=$directLogin['shopSno']?>" class="loginCheck" />
    <input type="hidden" name="aceTarget" value="<?=$directLogin['target']?>" class="loginCheck" />
</form>
<style rel="stylesheet" type="text/css">
    .acecounterDiv { display:none; }
    .agree-div { text-align:center;height:50px;/*line-height:50px;*/ }
    textarea { width:100%;height:100px; overflow-y:scroll; border:1px solid #D5D5D5;  }
    .ace-agreement { white-space:pre; height:200px; }
</style>
<script type="text/javascript">
    $(function () {
        var shopStatus = $("input[name='shopStatus']").val();
        if(shopStatus == "e") {
            $(".acecounterJoinDiv").show();
            $("input[name='mode']").val("regist");
        } else {
            $(".acecounterSettingDiv").show();
            $("input[name='mode']").val("modify");
        }

        // 에이스카운터 사용신청
        $(".btn-register").click(function() {
            if(!$("#acecounterServiceAgree").is(":checked") && shopStatus == 'e') {
                alert("이용약관에 동의해주세요.");
                return false;
            } else if(!$("#acecounterPrivateAgree").is(":checked") && shopStatus == 'e') {
                alert("개인정보 제3자 제공에 동의해주세요.");
                return false;
            }
            $("#acecounterForm").submit();
        });

        $(".btn-modify").click(function() {
            if($(":radio[name=aceCommonScriptFl]:checked").val() == 'n') {
                if(confirm("사용여부를 사용안함으로 선택하시면, 에이스카운터에 쇼핑몰 정보가 쌓이지 않아 통계분석이 되지 않습니다.\n계속하시겠습니까?")) {
                    $("#acecounterForm").submit();
                }
            } else {
                $("#acecounterForm").submit();
            }
        });

        $(".btn-ace-link").click(function () {
            var checks = true;
            $('.loginCheck').each(function () {
                if($(this).val() == '' || $(this).val() == undefined) {
                    checks = false;
                    return false;
                }
            });
            if(checks) {
                window.open('about:blank', "aceDirectLogin");
                $("#loginForm").attr({"action":"<?=$directLogin['action']?>", "target":"aceDirectLogin"}).submit();
            }
        });
    });
</script>
