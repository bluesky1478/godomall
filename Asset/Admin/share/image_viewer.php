<script type="text/javascript">
$(document).ready(function(){
	$('#imageArea').imageViewer({
		wrapEle: $('#image_viewer')
		, zoomRatioEle: $('#zoom')
		, btnAutoEle: $('#btnAuto')
		, btnOriEle: $('#btnOri')
	});
});
</script>
<div id="image_viewer">
	<div id="imageArea"><!-- 이미지출력영역 --></div>
	<div id="toolbar">
		<span id="zoom"><!-- 비율출력 --></span>
		<span class="button"><button id="btnAuto">화면사이즈에맞게</button></span>
		<span class="button"><button id="btnOri">원본사이즈</button></span>
	</div>
</div>
