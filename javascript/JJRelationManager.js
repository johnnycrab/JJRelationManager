(function($) {
	
	$.entwine('colymba', function($) {
		    		
		//@TODO prevent button click to call default url request
		$('#toggleRelationBtn').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},			
			onclick: function(e) {
				var url = $(this).data('url'),
					cacheBuster = new Date().getTime(),
					data = {};

				data.checked = $(this).attr('checked') ? 1 : 0;

				$.ajax({
						url: url + '?cacheBuster=' + cacheBuster,
						data: data,
						type: "POST",
						context: $(this)
					})
					.done(function() {
						$(this).parents('.ss-gridfield').entwine('.').entwine('ss').reload();
					});

				/*var action, url, data = {}, ids = [], cacheBuster;
				action = $('select#bulkActionName').val();
				
				if ( action != 'edit' )
				{				
					url = $(this).data('url');
					cacheBuster = new Date().getTime();
          
					$('.col-bulkSelect input:checked').each(function(){
						ids.push( parseInt( $(this).attr('name').split('_')[1] ) );
					});				
					data.records = ids;

					$.ajax({
						url: url + '/' + action + '?cacheBuster=' + cacheBuster,
						data: data,
						type: "POST",
						context: $(this)
					}).done(function() {
            $(this).parents('.ss-gridfield').entwine('.').entwine('ss').reload();
					});
				}*/				
			} 
		});		
	});
	
}(jQuery));