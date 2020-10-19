<?php /* Template_ 2.2.7 2016/05/02 11:34:31 D:\wamp\godo5\user\data\skin\front\food_story\popup\layer.html 000001783 */ ?>


<div id="<?php echo $data["sno"]?>_form"  style="margin:0 auto;width:100%; ">
    <div class="box">
        <div class="view"  style="width:100%; max-height: 700px; overflow: auto;border:1px solid #000000;">
            <?php echo $data["popupContent"]?>

        </div>

        <?php if($data["todayUnSeeFl"]=='y'){?>
            <!-- 오늘 하루 보이지 않음 : start -->
            <div class="check" style="background-color:<?php echo $data["todayUnSeeBgColor"]?>; color:<?php echo $data["todayUnSeeFontColor"]?>; text-align:<?php echo $data["todayUnSeeAlign"]?>;">
				<span class="form-element">
					<label for="todayUnSee" class="check-s"  style="background-color:<?php echo $data["todayUnSeeBgColor"]?>;">오늘 하루 보이지 않음</label>
					<input type="checkbox" id="todayUnSee" class="checkbox" onClick="popup_cookie('<?php echo $data["sno"]?>', this);">
				</span>
            </div>
            <!-- 오늘 하루 보이지 않음 : end -->
        <?php }?>
    </div>
</div>
