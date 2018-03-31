/* global variables */
var input_hover_active = null;


/* input-hover functions */

/**
 * \brief	save the value of the input element to local storage
 *
 * \param	input	input element
 */
function input_hover_store(input){
	var value = input.value;

	if(input.type == "checkbox")
		value = input.checked;

	set(input.id + '_value', value);
}

/**
 * \brief	show the input elements and buttons and hide the overlay
 * 			element of a hover master element
 * 			also make the input writeable and store its current
 * 			value to local storage if not already present
 *
 * \param	master	hover master element
 */
function input_hover_show(master){
	var input = document.getElementById(master.id + '-input');
	var overlay = document.getElementById(master.id + '-overlay');


	/* show input and update local storage */
	if(input != null){
		input.readOnly = false;
		input.style.display = 'inline-block';

		if(get(input.id + '_value') == null)
			input_hover_store(input);
	}

	/* hide overlay */
	if(overlay != null)
		overlay.style.display = 'none';

	/* show buttons */
	var i = 0;

	// -action-<num> buttons
	while(1){
		var btn = document.getElementById(master.id + '-action-' + i);

		if(btn == null)
			break;

		btn.style.visibility = 'visible';
		i++;
	}

	// -reset button
	var btn = document.getElementById(master.id + '-reset');

	if(btn != null)
		btn.style.visibility = 'visible';

}

/**
 * \brief	hide the input elements and buttons and show the overlay
 * 			element of a hover master element
 * 			also make the input readonly
 *
 * \param	master	hover master element
 */
function input_hover_hide(master){
	var input = document.getElementById(master.id + '-input');
	var overlay = document.getElementById(master.id + '-overlay');


	/* hide input */
	if(input != null){
		input.readOnly = true;
		input.style.display = 'none';
	}

	/* show overlay */
	if(overlay != null)
		overlay.style.display = 'inline-block';

	/* hide buttons */
	var i = 0;

	// -action-<num> buttons
	while(1){
		var btn = document.getElementById(master.id + '-action-' + i);

		if(btn == null)
			break;

		btn.style.visibility = 'hidden';
		i++;
	}

	// -reset button
	var btn = document.getElementById(master.id + '-reset');

	if(btn != null)
		btn.style.visibility = 'hidden';

}

/**
 * \brief	check if an input-hover element has been modified
 *
 * \param	master	the respective master element
 *
 * \return	true	the given element contains modifications
 * 			false	the given element does not contain modifications
 * 					or doesn't contain an -input element
 */
function input_hover_modified(master){
	var input = document.getElementById(master.id + '-input');

	if(input == null)
		return false;

	var value = input.value;

	if(input.type == "checkbox")
		value = input.checked;

	return (get(input.id + '_value') != value.toString());
}

/**
 * \brief	check if an input-hover element is focused
 *
 * \param	master	the respective master element
 *
 * \return	true	the given element has focus
 * 			false	the given element does not have focus
 */
function input_hover_focused(master){
	var input = document.getElementById(master.id + '-input');

	return (input != null && (document.activeElement == input));
}

/**
 * \brief	check if an active input-hover element exists and contains
 * 			modifications
 *
 * \return	true	active element exists and contains modifications
 * 			false	either no active element present or no modifications
 * 					for the active element
 */
function input_hover_active_modified(){
	return (input_hover_active != null && input_hover_modified(input_hover_active.parentNode));
}

/**
 * \brief	reset the given input-hover element's value to the value
 * 			stored in local storage
 *
 * \param	master	the respective input-hover master element
 */
function input_hover_reset(master){
	var input = document.getElementById(master.id + '-input');

	if(input == null)
		return;

	var value = get(input.id + '_value');

	if(input.type == "checkbox")
		input.checked = (value == 'true')
	else
		input.value = value;
}


/* callbacks for focus events */
function focusin_hdlr(e){
	/* do not allow to focus a second element if the current one
	 * contains modifications */
	if(input_hover_active_modified()){
		input_hover_active.focus();
		return false;
	}

	/* update the active element and show it */
	input_hover_active = this;
	input_hover_show(this.parentNode);
}

function focusout_hdlr(e){
	/* hide the current element if it has not been modified */
	if(!input_hover_modified(this.parentNode)){
		input_hover_hide(this.parentNode);
		input_hover_active = null;
	}
}

/* callbacks for mouse events */
function mouseenter_hdlr(e){
	/* show input-hover elements for a second element if the current
	 * one does not contain modifications */
	if(!input_hover_active_modified())
		input_hover_show(this);
}

function mouseout_hdlr(e){
	/* hide input-hover element if the element is neither focused
	 * nor modified */
	if(!input_hover_modified(this) && !input_hover_focused(this))
		input_hover_hide(this);
}

/* callbacks for click events */
function reset_click_hdlr(e){
	input_hover_reset(this.parentNode);
}

/* callbacls for form submission */
function submit(e){
	event.preventDefault();

	/* trigger ajax processing */
	$.ajax({
		method: 'post',
		url: this.action,
		dataType: "text",
		data : $(this).serialize(),
		success: function(msg, status, data){
			if(!input_hover_active)
				return;

			/* update the value of the input-hover elements */
			input_hover_store(input_hover_active);
			document.getElementById(input_hover_active.parentNode.id + '-overlay').value = get(input_hover_active.id + '_value');

			/* hide the input-hover element */
			input_hover_hide(input_hover_active.parentNode);
	 	},
		error: function(xhr, desc, err){
			alert("error invoking submit action: " + err);
		}
	});
}


/* document functions */
$(document).ready(function(){
	/* register mouseover and mouseout events for master elements */
	masters = document.getElementsByClassName('input-hover-master');

	for(var i=0; i<masters.length; i++){
		masters[i].addEventListener('mouseover', mouseenter_hdlr);
		masters[i].addEventListener('mouseout', mouseout_hdlr);
	}

	/* register reset action for reset buttons */
	for(var i=0; i<masters.length; i++){
		btn = document.getElementById(masters[i].id + '-reset');

		if(btn != null)
			btn.addEventListener('click', reset_click_hdlr);
	}

	/* hide button elements */
	buttons = document.getElementsByClassName("input-hover-button");

	for(var i=0; i<buttons.length; i++)
		buttons[i].style.visibility = 'hidden';

	/* register focusin and focus out events for input elements */
	inputs = document.getElementsByClassName("input-hover-input");

	for(var i=0; i<inputs.length; i++){
		inputs[i].addEventListener('focusin', focusin_hdlr);
		inputs[i].addEventListener('focusout', focusout_hdlr);
	}

	/* remove local storage entries for input values */
	for(var i=0; i<inputs.length; i++)
		rm(inputs[i].id + '_value');

	/* register submit handlers for input-hover forms */
	forms = document.getElementsByClassName("input-hover-form");

	for(var i=0; i<forms.length; i++){
		forms[i].addEventListener('submit', submit);
	}
});
