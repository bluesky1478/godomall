<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{mallTitle}</title>
    <style type="text/css">
        .blackout {
            width: 578px;
            margin: 12% auto 0;
            padding: 210px 0 90px;
            background: url("<?=PATH_ADMIN_GD_SHARE?>img/icon_limit.png") no-repeat center 69px;
            text-align: center;
        }

        .blackout > strong {
            color: #222222;
            font-size: 22px;
        }

        .blackout > strong > em {
            font-style: normal;
            color: #EC1C23;
        }

        * {
            font-family: Malgun Gothic, "맑은 고딕", AppleGothic, Dotum, "돋움", sans-serif;
        }

        .blackout p {
            padding: 15px 0 0;
            color: #222222;
            font-size: 13px;
            line-height: 1.5;
            color: #666666;
        }

        /*20160510 윤태건 404error*/
        * {
            margin: 0;
            padding: 0;
        }

        body {
            -webkit-text-size-adjust: none;
        }

        .c-point {
            color: #EC1C23;
        }

        .submitbtn .skinbtn {
            width: 130px;
        }

        .skinbtn {
            display: inline-block;
            *display: inline;
            *zoom: 1;
            height: 40px;
            padding: 0 5px;
            text-align: center;
            vertical-align: top;
            box-sizing: border-box;
            cursor: pointer;
            font-size: 14px;

            border: 1px solid #B1B1B1;
            background: #FFFFFF;
            color: #777777;
        }

        .btn-m2 {
            height: 44px;
        }

        .submitbtn {
            margin: 20px 0 0;
        }

        @media (max-width: 768px) {
            .blackout {
                width: auto;
                padding: 190px 0 90px;
                /*background-size: 97px 84px;*/
            }

            .blackout > strong {
                font-size: 20px;
            }

            .blackout p {
                padding: 10px 0 0;
                font-size: 14px;
                line-height: 18px;
                letter-spacing: -0.05em;
            }

            .submitbtn {
                margin: 20px 0 0;
            }
        }
    </style>
    <script type="text/javascript">
        $(function() {
            $("#btnReload").click(function(e){
                window.location.reload();
            });
            $("#btnGoBack").click(function(e){
                window.history.back();
            });

            // 열려있는 다이얼로그 닫기
            if (top.BootstrapDialog != undefined) {
                var dialogs = top.BootstrapDialog.dialogs;
                $.get('../base/layer_screen_security.php', '', function (data) {
                    BootstrapDialog.show({
                        title: '화면보안접속',
                        message: $(data),
                        closable: true
                    });
                });
            }
        });
    </script>
</head>
<body>
<div class="blackout">
    <strong>본 페이지는 <br><span class="c-point">화면보안접속</span>이 설정되어 있습니다.</strong>
    <p>
        운영자에 등록된 정보로 인증 완료 후 접속이 가능합니다.
    </p>
    <div class="submitbtn">
        <button type="button" class="skinbtn default btn-m2" id="btnGoBack"><b>뒤로가기</b></button>
        <button type="button" class="skinbtn default btn-m2" id="btnReload"><b>새로고침</b></button>
    </div>
</div>
</body>
</html>


</script>
