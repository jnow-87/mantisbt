/* helper */

/**
 * \brief	handler to be called when hiding an element
 * 			the hiding state will be stored in local storage
 */
function hr_hide_hdlr(){
	set(this.id + '_hidden', 1);
}

/**
 * \brief	handler to be called when showing an element
 * 			the hiding state will be stored in local storage
 */
function hr_show_hdrl(){
	set(this.id + '_hidden', 0);
}


/**
 * \brief	prepare hr sections, such that
 * 				- hide/show handlers are registered
 * 				- each section's hidden state is updated
 * 				  according to the state stored in local
 * 				  storage
 */
$(document).ready(function(){
	var hrs = document.getElementsByClassName('hr-text');


	/* register callbacks for hide/show events */
	for(var i=0; i<hrs.length; i++){
		var tgt = $('#' + hrs[i].id + '_target');

		tgt.on('show.bs.collapse', hr_show_hdrl);
		tgt.on('hide.bs.collapse', hr_hide_hdlr);
	}

	/* update hidden state of all sections */
	// disable transition effects, globally
	var transition = $.support.transition;
	$.support.transition = false;

	for(var i=0; i<hrs.length; i++){
		var tgt = $('#' + hrs[i].id + '_target');
		var hidden = get(hrs[i].id + '_target_hidden');

		if(hidden == 1)			tgt.collapse('hide');
		else if(hidden == 0)	tgt.collapse('show');
	}

	// restore transition effect configuration
	$.support.transition = transition;
});
