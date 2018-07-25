/* configure DataTables and assign it all tables with class table-sortable */
function mantis_table_init(){
	$('table.table-datatable').DataTable({
		'retrieve': true,
		'dom': '<"pull-left"il><"pull-right"f><"table-center"p><t><"pull-left"il><"pull-right"f><"table-center"p>',
		'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
		'paging': false,
		'scrollY': '300px',
		'scrollCollapse': true,
		'ordering': true,
		'columnDefs':[{
			'targets': 'no-sort',
			'orderable': false,
		}],
	});

	$('table.table-searchable').DataTable({
		'retrieve': true,
		'dom': '<"pull-left"il><"pull-right"f><"table-center"p><t><"pull-left"il><"pull-right"f><"table-center"p>',
		'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
		'searching': true,
		'paging': true,
		'ordering': false,
	});

	$('table.table-sortable').DataTable({
		'retrieve': true,
		'dom': '',
		'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
		'paging': false,
		'searching': false,
		'ordering': true,
		'columnDefs':[{
			'targets': 'no-sort',
			'orderable': false,
		}],
	});
}

/* document change handler */
$(document).ready(mantis_table_init);
$('body').on('user_event_body_changed', mantis_table_init);


/* onclick handler for table rows, calling the given url */
$('tr.tr-url').click(function(e){
	window.location = $(this).data('url');
});
