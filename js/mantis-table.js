/* configure DataTables and assign it all tables with class table-sortable */
$(document).ready(function(){
	$('table.table-sortable').DataTable({
		'dom': '<"pull-left"il><"pull-right"f><"center"p><t><"pull-left"il><"pull-right"f><"center"p>'
	});
});

/* onclick handler for table rows, calling the given url */
$('tr.tr-url').click(function(e){
	window.location = $(this).data('url');
});
