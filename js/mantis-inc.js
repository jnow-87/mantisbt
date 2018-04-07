/* local storage operations */

/**
 * \brief	store key, value pair in local storage
 *
 * \param	key		identifier to be used
 * \param	value	string to be stored
 *
 * \return	nothing
 */
function set(key, value){
	localStorage.setItem(key, value);
}

/**
 * \brief	get value of key from local storage
 *
 * \param	key		identifier to be used
 *
 * \return	associated value
 */
function get(key){
	return localStorage.getItem(key);
}

/**
 * \brief	remove an entry from local storage
 *
 * \param	key		identifier to be removed
 *
 * \return	nothing
 */
function rm(key){
	localStorage.removeItem(key);
}


/* status bar messages */
var next = 0;			// time [ms] till the next status bar message can be shown
var show_time = 3000;	// time [ms] that a status bar message shall be displayed

/**
 * \brief	display a message in the status bar
 *
 * \param	type	message type, either
 * 						'success'	display message as success
 * 						'error'		display message as error
 * 						'warning'	display message as warning
 * 						'html'		assume message to render a html page, redering
 * 									it in a new window. Additionally showing a
 * 									warning message.
 *
 * \param	msg		message string
 */
function statusbar_print(type, msg){
	if(type == 'error'){
		var actionbar = document.getElementById('statusbar-err');
	}
	else if(type == 'success'){
		var actionbar = document.getElementById('statusbar-ok');
	}
	else if(type == 'warning'){
		var actionbar = document.getElementById('statusbar-warn');
	}
	else if(type == 'html'){
		var actionbar = document.getElementById('statusbar-warn');
		var html = msg;
		msg = 'Rendering result in container';
	}
	else
		throw 'unexpected response type \'' + type + '\', msg: "' + msg + '"';

	/* display time calculation */
	var now = (new Date).getTime();
	var show_delay  = next - now;

	if(show_delay < 0)
		show_delay = 0;

	next = now + show_time + show_delay;

	/* show timeout */
	setTimeout(function(){
			$(actionbar).trigger('visible');
			actionbar.style.display = 'block';
			actionbar.style.visibility = 'visible';
			actionbar.value = msg;

			if(typeof html !== 'undefined'){
				var el = document.createElement('div');
				el.style = 'position:absolute; width: 100%; height: 100%; top: 0; padding:200px 300px 200px 300px; z-index:5000;';
				el.innerHTML = '<div style="background:white">' + html + '</div>';
				document.body.appendChild(el);
				$('body').trigger('domChanged');
			}
		},
		show_delay + 10	// +10 ensures the next show occurs after the hide
	);

	/* hide timeout */
	setTimeout(function(){
			actionbar.style.visibility = 'hidden';
			actionbar.style.display = 'none';
		},
		show_time + show_delay
	);
}

$('#statusbar-warn').on('visible', function(){
		console.log("changes");
	}
);
