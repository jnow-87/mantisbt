/* configure DataTables and assign it all tables with class table-sortable */
$(document).ready(function(){
	$('table.table-paging').DataTable({
		'dom': '<"pull-left"il><"pull-right"f><"table-center"p><t><"pull-left"il><"pull-right"f><"table-center"p>',
		'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
		'searching': false,
		'paging': true,
		'ordering': false,
	});

	$('table.table-searchable').DataTable({
		'dom': '<"pull-left"il><"pull-right"f><"table-center"p><t><"pull-left"il><"pull-right"f><"table-center"p>',
		'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
		'searching': true,
		'paging': true,
		'ordering': false,
	});

	$('table.table-sortable').DataTable({
		'dom': '<"pull-left"il><"pull-right"f><"table-center"p><t><"pull-left"il><"pull-right"f><"table-center"p>',
		'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
		'paging': true,
		'ordering': true,
		'columnDefs':[{
			'targets': 'no-sort',
			'orderable': false,
		}],
	});
});

/* onclick handler for table rows, calling the given url */
$('tr.tr-url').click(function(e){
	window.location = $(this).data('url');
});
