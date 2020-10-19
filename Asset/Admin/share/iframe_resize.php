IFRAME 높이 리사이징 스크립트
<script language="javascript">
<!--
var name = "<?=$_GET['name']?>";
var height = "<?=$_GET['height']?>";
if (name !='' && height !='' && parent.parent.document.getElementsByName(name)[0])
{
    parent.parent.document.getElementsByName(name)[0].height = height;
}
//-->
</script>
