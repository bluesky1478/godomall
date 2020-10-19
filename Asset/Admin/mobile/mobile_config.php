<form id="frmMobileShop" name="frmMobileShop" action="./mobile_ps.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="mode" value="mobile_config" />
<input type="hidden" name="mobileShopIconTmp" value="<?php echo $data['mobileShopIcon']; ?>"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location);?> <small>모바일샵 사용에 대한 기본적인 설정입니다.</small></h3>
        <input type="submit" value="저장" class="btn btn-red">
    </div>

    <div class="table-title gd-help-manual">
        모바일샵 사용 여부
    </div>
    <table class="table table-cols">
    <colgroup><col class="width-md" /><col/></colgroup>
    <tr>
        <th>사용 여부</th>
        <td>
            <label class="radio-inline"><input type="radio" name="mobileShopFl" value="y" <?php echo gd_isset($checked['mobileShopFl']['y']);?> />사용함</label>
            <label class="radio-inline"><input type="radio" name="mobileShopFl" value="n" <?php echo gd_isset($checked['mobileShopFl']['n']);?> />사용안함</label>
        </td>
    </tr>
    <?php if ($data['mobileShopFl'] == 'y') {?>
    <tr>
        <th>모바일샵 주소</th>
        <td class="form-inline">
            <?php if ($browserCheck === true) {?>
            <input type="button" class="btn btn-sm btn-gray" value="모바일샵 미리보기" onclick="window.open('<?php echo URI_MOBILE;?>', 'mobileShop', 'width=375, height=667, scrollbars=yes');" />
            <?php } else {?>
            <input type="button" class="btn btn-sm btn-gray" value="모바일샵 미리보기" onclick="alert('현재 사용하시는 Browser로는 모바일샵을 보실 수 없습니다.')" />
            <?php }?>
            <label>
                <?php echo URI_MOBILE;?>
            </label>
        </td>
    </tr>
    <?php }?>
    <tr>
        <th>홈 화면 아이콘</th>
        <td class="form-inline">
            <input type="file" class="upload" id="mobileShopIcon" name="mobileShopIcon">
            <ul class="notice-info">
                <li>권장 이미지 사이즈 : 100px x 100 px  / 500kb이하</li>
                <li>권장 확장자 : png</li>
            </ul>
            <div>
                <?php
                if (empty($data['mobileShopIcon']) === false) {
                    echo gd_html_image($data['mobileShopIcon'], '바로가기 아이콘');
                    if (stripos($data['mobileShopIcon'], 'commonimg') === false) {
                        echo '<label class="checkbox-inline" style="padding-left:10px"><input type="checkbox" name="mobileShopIconDel" value="y" />체크 시 삭제</label>';
                    }
                }
                ?>
            </div>
        </td>
    </tr>
    </table>

    <input type="hidden" name="mobileShopGoodsFl" value="each" /> <!-- 상품 출력 : 추후 지원함. 현재는 모바일샵 별도 출력상태 적용 -->
    <input type="hidden" name="mobileShopCategoryFl" value="same" /> <!-- 카테고리 출력 : 추후 지원함. 현재는 "온라인 쇼핑몰 출력상태와 동일하게 적용"으로 설정-->
    <!--
    <div class="table-title gd-help-manual">
        모바일샵 출력상태 설정
    </div>
    <table class="table table-cols">
    <colgroup><col class="width-md" /><col/></colgroup>
    <tr>
        <th>상품 출력</th>
        <td>
            <div class="radio">
                <label><input type="radio" name="mobileShopGoodsFl" value="same" <?php echo gd_isset($checked['mobileShopGoodsFl']['same']);?> />온라인 쇼핑몰 출력상태와 동일하게 적용</label>
            </div>
            <div class="radio">
                <label><input type="radio" name="mobileShopGoodsFl" value="each" <?php echo gd_isset($checked['mobileShopGoodsFl']['each']);?> />모바일샵 별도 출력상태 적용</label>
            </div>
        </td>
    </tr>
    <tr>
        <th>카테고리 출력</th>
        <td>
            <p class="notice-info mgb0">
                추후 지원함. 현재는 &quot;온라인 쇼핑몰 출력상태와 동일하게 적용&quot;으로 설정
            </p>
            <label><input type="radio" name="mobileShopCategoryFl" value="same" <?php echo gd_isset($checked['mobileShopCategoryFl']['same']);?> />온라인 쇼핑몰 출력상태와 동일하게 적용</label><br />
            <label><input type="radio" name="mobileShopCategoryFl" value="each" <?php echo gd_isset($checked['mobileShopCategoryFl']['each']);?> />모바일샵 별도 출력상태 적용</label>
        </td>
    </tr>
    </table>
    -->
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function(){
        $("#frmMobileShop").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
                return false;
            },
            rules: {
            },
            messages: {
            }
        });
    });
    //-->
</script>
