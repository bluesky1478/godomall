/**
 * Multi Select Box - jQuery plugin
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */

/**
 * 생성한 Select Box 에 순차적으로 option 값을 넣는 것임
 * JSON을 이용하여, option 선택시 다음 select box에 값이 있는지를 ajax post를 이용해서 불러옴
 * @param String Select box default ID
 * @param integer Select box 갯수
 * @param String post 로 넘길 URL
 * @param String Select box 의 첫번째 option index name
 * <code>
 * JSON 예제 : [{"optionValue":"001","optionText":"test"},{"optionValue":"002","optionText":"sample"}]
 * <script type="text/javascript">
 * $(function() {
 *     $('#testSample1').multi_select_box('#testSample',2,'test.php','선택하세요');
 *     $('#testSample2').multi_select_box('#testSample',2,'test.php','선택하세요');
 * });
 * </script>
 * </code>
 */
(function($){
$.fn.multi_select_box = function(selectID, cnt, url, emptyText) {

	return this.each(function(index, domEle) {

		$(this).change( function(event)
		{
			var thisID		= event.currentTarget.id;
			var selectPanel	= parseInt(thisID.substr((selectID.length - 1),1)) + 1;
			var targetID	= selectID + selectPanel;
			var parameters	= { mode:'next_select', value:$(this).val() };
			var tmpID		= '';

			postCallback	= function(data)
			{
				if (emptyText !='' && emptyText != null)
				{
					for (var i = selectPanel; i <= cnt; i++)
					{
						tmpID	= selectID + i;
						$(tmpID).html('<option value="">'+emptyText+'</option>');
					}
				}
				data = eval(data);
				if (data !='' && data != null) {
					$(targetID).html('');
					var k = 1;
					for (var j = 0; j < data.length; j++)
					{
						if(j == 0)
						{
							$(targetID).get(0).add(new Option(emptyText,''), document.all ? j : null);
						}
						k	= k + j

						$('<option/>', {
							text: data[j]['optionText'],
							value: data[j]['optionValue'],
							data: {
								flag: data[j]['flag'],
								mallName: data[j]['mallName']
							}
						}).appendTo(targetID);

						//$(targetID).get(0).add(new Option(data[j]['optionText'],data[j]['optionValue'],data: {url: 'some_data',whatever: 1}), document.all ? k : null);
					}
				}
			}

			$.post( url, parameters, postCallback );
		});

	});

}
})(jQuery);
