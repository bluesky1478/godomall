<style type="text/css">
    .blackout {
        width:578px;
        margin:12% auto 0;
        padding:210px 0 90px;
        background:url('<?=PATH_ADMIN_GD_SHARE?>img/icon_limit.png') no-repeat center 69px;
        text-align:center;
    }

    .blackout > strong {
        color:#222222;
        font-size:22px;
    }

    .blackout > strong > em {
        font-style:normal;
        color:#EC1C23;
    }

    * {
        font-family:Malgun Gothic, "맑은 고딕", AppleGothic, Dotum, "돋움", sans-serif;
    }

    .blackout p {
        padding:15px 0 0;
        color:#222222;
        font-size:13px;
        line-height:1.5;
        color:#666666;
    }

    /*20160510 윤태건 404error*/
    * {
        margin:0;
        padding:0;
    }

    body {
        -webkit-text-size-adjust:none;
    }

    .c-point {
        color:#EC1C23;
    }

    .submitbtn .skinbtn {
        width:130px;
    }

    .skinbtn {
        display:inline-block;
        *display:inline;
        *zoom:1;
        height:40px;
        padding:0 5px;
        text-align:center;
        vertical-align:top;
        box-sizing:border-box;
        cursor:pointer;
        font-size:14px;
        border:1px solid #B1B1B1;
        background:#FFFFFF;
        color:#777777;
    }

    .btn-m2 {
        height:44px;
    }

    .submitbtn {
        margin:20px 0 0;
    }

    @media (max-width:768px) {
        .blackout {
            width:auto;
            padding:190px 0 90px;
            /*background-size: 97px 84px;*/
        }

        .blackout > strong {
            font-size:20px;
        }

        .blackout p {
            padding:10px 0 0;
            font-size:14px;
            line-height:18px;
            letter-spacing:-0.05em;
        }

        .submitbtn {
            margin:20px 0 0;
        }
    }
</style>
<script type="text/javascript">
    $(function () {
        $('#btnGoBack').click(function (e) {
            window.history.back();
        });
    });
</script>
<div class="blackout">
    <strong>본 페이지는
        <span class="c-point">접속 권한이 없습니다.</span>
    </strong>
    <p><?= $title; ?>의 접속 권한은 대표운영자에게 문의하시기 바랍니다.</p>
    <div class="submitbtn">
        <button type="button" class="skinbtn default btn-m2" id="btnGoBack"><b>뒤로가기</b></button>
    </div>
</div>
