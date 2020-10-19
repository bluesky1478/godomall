<form id="frm" action="../member/member_ps.php" method="post">
    <div class="page-header js-affix">
        <h3>회원 등록 </h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./member_list.php');"/>
            <input type="button" value="저장" class="btn btn-red btn-register">
        </div>
    </div>
    <input type="hidden" name="mode" id="mode" value="register"/>
    <?php include('_member_view.php'); ?>
    <?php include('_member_view_business.php'); ?>
    <?php include('_member_view_other.php'); ?>
</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/member.js"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/member2.js"></script>
<script type="text/javascript">
    member2.init($('#frm'));

    $('.btn-register').click({form: $('#frm')}, member2.save);

    function trigger_header_red_button() {
        $('.page-header .btn-red').trigger('click');
    }
</script>
