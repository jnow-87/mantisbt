var inline_page_last_scoll_x = 0;
var inline_page_last_scoll_y = 0;
var inline_page_reload = false;

/**
 * \brief	create a div which overlays the current page, rendering the given
 * 			html code
 *
 * \param	html	html code to render
 */
function inline_page_create(html){
	/* auto-closing existing inline page */
	inline_page_close(true);

	/* create inline-page element */
	var el = document.createElement('div');
	el.className = 'inline-page-frame';
	el.id = 'inline-page';

	el.innerHTML = html;

	/* show inline page */
	document.body.appendChild(el);
	$('body').trigger('user_event_body_changed');

	/* scroll to top of page */
	// save current scroll position
	inline_page_last_scoll_x = window.scrollX;
	inline_page_last_scoll_y = window.scrollY;

	window.scrollTo(0, 0);
}

/**
 * \brief	close an existing inline page
 *
 * \param	prevent_reload	overwriting inline_page_reload, i.e. do not	reload
 * 							current page, even if inline-page-reload is set true
 */
function inline_page_close(prevent_reload){
	var page = document.getElementById('inline-page');

	if(page == null)
		return;

	if(inline_page_reload && !prevent_reload){
		inline_page_reload = false;
		location.reload();
	}

	/* remove inline page */
	document.body.removeChild(page);
	$('body').trigger('user_event_body_changed');

	/* restore previous scroll position */
	window.scrollTo(inline_page_last_scoll_x, inline_page_last_scoll_y);
}

/**
 * \brief	trigger a link using ajax to show the target content
 * 			within an inline page
 */
function inline_page_open_link(e){
	e.preventDefault();

	var action = $(this).attr('href');
	var reload = $(this).attr('inline-page-reload');

	if(reload != null)
		inline_page_reload = true;

	$.ajax({
		url: action,
		success: function(msg, status, data){
			/* display status message */
			try{
				var resp = JSON.parse(msg);

				for(var i=0; i<resp.length; i++)
					statusbar_print(resp[i].type, resp[i].msg);
			}
			catch(e){
				statusbar_print('html', msg);
			}
	 	},
		error: function(xhr, desc, err){
			statusbar_print('error', err);
		}
	});
}

/**
 * \brief	event handler for inline-page close
 */
function inline_page_close_hdlr(){
	inline_page_close(false);
}

/**
 * \brief	register handlers required for inline pages
 */
function inline_page_init(){
	/* register click handler for inline-page close button */
	btn = document.getElementsByClassName('inline-page-close');

	for(var i=0; i<btn.length; i++)
		btn[i].addEventListener('click', inline_page_close_hdlr);

	/* register click handler to inline-page links */
	links = document.getElementsByClassName('inline-page-link');

	for(var i=0; i<links.length; i++)
		links[i].addEventListener('click', inline_page_open_link);

	/* register submit handler for inline-page forms */
	forms = document.getElementsByClassName('inline-page-form');

	for(var i=0; i<forms.length; i++)
		forms[i].addEventListener('submit', inline_page_close_hdlr);

	/* register datetimepicker for elements on inline page */
	el = document.getElementsByClassName('inline-page-datetime');

	for(var i=0; i<el.length; i++){
		var locale = $(el[i]).attr('data-picker-locale');
		var format = $(el[i]).attr('data-picker-format');

		$(el[i]).datetimepicker({
			format: format,
			locale: locale,
		});
	}
}

/* document change handler */
$(document).ready(inline_page_init);
$('body').on('user_event_body_changed', inline_page_init);
