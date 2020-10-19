<form id="frm" action="../member/member_ps.php" method="post">
    <div class="page-header js-affix">
        <h3>회원 수정 </h3>
        <input type="button" value="저장" class="btn btn-red btn-register">
    </div>
    <input type="hidden" name="mode" id="mode" value="<?= $mode ?>"/>
    <input type="hidden" name="memNo" id="memNo" value="<?= $data['memNo'] ?>"/>
    <?php include('_member_view.php'); ?>
    <?php include('_member_view_business.php'); ?>
    <?php include('_member_view_other.php'); ?>
</form>
<?php include('_member_view_history.php'); ?>
<?php
    // 모바일앱 사용정보
    if (empty($appDeviceInfo) === false || empty($installBenefitFl) === false) {
        include('_member_view_app_device.php');
    }
?>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/member.js"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/member2.js"></script>
<script type="text/javascript">
    member2.init($('#frm'));
    member2.set_my_page(true);
    $('.btn-register').click({form: $('#frm')}, member2.save);

    $('#emailDomain').removeClass('error');
    $('#nickNm').removeClass('error');
    $('#busiNo').removeClass('error');

    <?php if($groupCouponConditionManual == 'y') { ?>
    var beforeSno = '<?=$data["groupSno"]?>';
    $("#groupSno").on('change', function(){
        var sno = $(this).val();
        if(sno > 0 && sno != beforeSno) {
            $.ajax({
                "url": "../member/member_group_ps.php",
                "method": "post",
                "data": {
                    "mode": "getGroupCoupon",
                    "sno": sno
                }
            }).done(function (data) {
                if(data.couponNo != "") {
                    $.ajax({
                        "url": "../promotion/coupon_ps.php",
                        "method": "post",
                        "data": {
                            "mode": "checkCouponType",
                            "couponNo": data.couponNo
                        }
                    }).done(function (data) {
                        if(!data.isSuccess) {
                            var message = '회원등급 변경으로 지급예정인 쿠폰 중 발급이 종료된 쿠폰이 있습니다.<br/>발급종료 쿠폰은 제외하고 쿠폰이 발급됩니다.<br/>회원등급별 혜택은 <a href="../member/member_group_list.php" target="_blank" class="btn-link-underline">[회원>회원 관리>회원등급관리]</a>에서 설정할 수 있습니다.';
                            alert(message)
                        }
                    });
                }
            });
        }
    })
    <?php } ?>
</script>
