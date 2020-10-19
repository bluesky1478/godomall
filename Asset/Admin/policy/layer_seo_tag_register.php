<form id="frmSeoTag" name="frmBank" action="base_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="<?php echo $data['mode']; ?>"/>
    <input type="hidden" name="sno" value="<?php echo gd_isset($data['sno']); ?>"/>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>페이지 분류</th>
            <td>
                <input type="hidden" name="oriDeviceFl" value="<?php echo $data['deviceFl']; ?>"/>
                <label class="radio-inline">
                    <input type="radio" name="deviceFl" value="p" <?php echo gd_isset($checked['deviceFl']['p']); ?> />PC 쇼핑몰
                </label>
                <label class="radio-inline">
                    <input type="radio" name="deviceFl" value="m" <?php echo gd_isset($checked['deviceFl']['m']); ?> />모바일 쇼핑몰
                </label>
            </td>
        </tr>
        <tr>
            <th>페이지 경로</th>
            <td class="form-inline">
                <input type="hidden" name="oriPath" value="<?php echo $data['path']; ?>" class="form-control width-lg"/> http://<span class="js-device-fl <?php if ($data['deviceFl'] != 'm') { ?>display-none<?php } ?>">m.</span><?= $mallDomain ?>/<input type="text" name="path" value="<?php echo $data['path']; ?>" class="form-control width-lg"/>
                <div class="notice-info">
                    개별 SEO 태그를 설정할 페이지의 경로를 정확하게 입력해주세요.<br/> ex) service/company.php
                </div>

            </td>
        </tr>
        <?php foreach ($seoConfig['tag'] as $k => $v) { ?>
            <tr>
                <th> <?= $v ?></th>
                <td>
                    <input type="text" name="<?= $k ?>" value="<?php echo $data[$k]; ?>" class="form-control"/>
                </td>
            </tr>
        <?php } ?>
    </table>
    <div class="notice-info">
        태그 입력 시, ‘쇼핑몰 이름’ {seo_mallNm} 치환코드 사용이 가능합니다.
    </div>
    <div class="notice-danger">
        기타 페이지 SEO태그 설정에서는 주요 페이지 (상품, 카테고리, 브랜드, 기획전, 게시판) SEO 태그 설정은 불가합니다.
    </div>
    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
        <input type="submit" value="저장" class="btn btn-lg btn-black"/>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $("input[name='deviceFl']").click(function () {
            if ($(this).val() == 'm') {
                $(".js-device-fl").show();
            } else {
                $(".js-device-fl").hide();
            }
        });


        $('#frmSeoTag').validate({
            rules: {
                path: {
                    required: true
                }
            },
            messages: {
                path: {
                    required: "페이지 경로를 입력해주세요."
                }
            },
            submitHandler: function (form) {
                var params = $(form).serializeArray();

                $.ajax('./base_ps.php', {
                    method: "post",
                    data: params,
                    success: function () {
                        var response = $.parseJSON(arguments[0]);
                        close_validate_process_dialog();
                        if (response.result) {
                            setTimeout(function () {
                                layer_close();
                                alert('저장이 완료되었습니다.');
                                get_seo_tag_list(<?=$page?>, '<?=$targetDiv?>');
                            }, 1000);

                        } else {
                            setTimeout(function () {
                                close_validate_process_dialog();
                                if (response.code === "NOT FILE") {
                                    alert('해당 페이지가 존재하지 않습니다. 다시 확인해주세요.');
                                } else if (response.code === "COMMON") {
                                    alert('주요 페이지 (상품, 카테고리, 브랜드, 기획전, 게시판) SEO 태그 설정은 불가합니다.<br/>주요 페이지 SEO 태그 또는 개별 SEO 설정에 적용하시기 바랍니다.');
                                } else {
                                    alert('이미 SEO태그가 적용된 페이지 경로입니다. 다시 확인해주세요.');
                                }
                            }, 1000);
                        }
                    }
                });
            }
        });

    });
    //-->
</script>
