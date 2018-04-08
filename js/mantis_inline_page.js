/**
 * \brief	create a div which overlays the current page, rendering the given
 * 			html code
 *
 * \param	html	html code to render
 */
function inline_page_create(html){
	var el = document.createElement('div');
	el.className = 'inline-page-frame';
	el.id = 'inline-page';

	el.innerHTML = html;

	document.body.appendChild(el);
	$('body').trigger('user_event_body_changed');
}

/**
 * \brief	close an existing inline page
 */
function inline_page_close(){
	var page = document.getElementById('inline-page');

	if(page != null){
		document.body.removeChild(page);
		$('body').trigger('user_event_body_changed');
	}
}

/**
 * \brief	register handlers required for inline pages
 */
function inline_page_init(){
	/* register click handler for inline-page close button */
	btn = document.getElementsByClassName('inline-page-close');

	for(var i=0; i<btn.length; i++)
		btn[i].addEventListener('click', inline_page_close);


	/* register submit handler for inline-page forms */
	forms = document.getElementsByClassName('inline-page-form');

	for(var i=0; i<forms.length; i++)
		forms[i].addEventListener('submit', inline_page_close);
}

/* document change handler */
$(document).ready(inline_page_init);
$('body').on('user_event_body_changed', inline_page_init);
