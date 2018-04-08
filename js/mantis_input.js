/* global variables */
var input_hover_active = null;
var input_hover_active_all = false;


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
 * \brief	update the value stored for the input element
 * 			and the value displayed by the overlay element
 *
 * \param	master	hover master element
 */
function input_hover_update(master){
	var input = document.getElementById(master.id + '-input');
	var overlay = document.getElementById(master.id + '-overlay');

	if(input == null || overlay == null)
		return;

	// update local storage and overlay with current value
	input_hover_store(input);

	if(input.type == 'select-one'){
		var value = get(input.id + '_value');

		for(var i=0; i<input.options.length; i++){
			if(input.options[i].value == value)
				overlay.value = input.options[i].text;
		}
	}
	else
		overlay.value = get(input.id + '_value');
}

/**
 * \brief	show the input elements and buttons and hide the overlay
 * 			element of a hover master element
 * 			buttons are only shown if input_hover_show_all is set to false
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

	if(input_hover_active_all)
		return 

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
 * 			these operations are only performed if input_hover_show_all
 * 			is set to false
 * 			also make the input readonly
 *
 * \param	master	hover master element
 */
function input_hover_hide(master){
	var input = document.getElementById(master.id + '-input');
	var overlay = document.getElementById(master.id + '-overlay');


	if(input_hover_active_all)
		return;

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
 * \brief	show the inputs of all input-hover elements, hiding their overlay
 * 			show the reset-all and submit-all button, hiding the show-all button
 */
function input_hover_show_all(){
	/* enable active_all mode */
	input_hover_active_all = true;
	input_hover_active = null;

	/* show inputs, hiding overlays */
	var masters = document.getElementsByClassName('input-hover-master');

	for(var i=0; i<masters.length; i++){
		input_hover_show(masters[i]);
	}

	/* toggle show-all, reset-all, submit-all buttons */
	var show_all = document.getElementById('input-hover-show-all');
	var reset_all = document.getElementById('input-hover-reset-all');
	var submit_all = document.getElementById('input-hover-submit-all');
	
	show_all.style.display = 'none';
	reset_all.style.display = 'inline-block';
	submit_all.style.display = 'inline-block';
}

/**
 * \brief	hide the inputs of all input-hover elements, showing their overlays
 *			hide the reset-all and submit-all button, showing the show-all button
 */
function input_hover_hide_all(){
	/* disable active_all mode */
	input_hover_active_all = false;

	/* hide inputs, showing their overlays */
	var masters = document.getElementsByClassName('input-hover-master');

	for(var i=0; i<masters.length; i++){
		input_hover_reset(masters[i]);
		input_hover_hide(masters[i]);
	}

	/* toggle show-all, reset-all, submit-all buttons */
	var show_all = document.getElementById('input-hover-show-all');
	var reset_all = document.getElementById('input-hover-reset-all');
	var submit_all = document.getElementById('input-hover-submit-all');
	
	show_all.style.display = 'inline-block';
	reset_all.style.display = 'none';
	submit_all.style.display = 'none';
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
function input_hover_focusin_hdlr(e){
	/* do not allow to focus a second element if the current one
	 * contains modifications */
	if(input_hover_active_modified()){
		input_hover_active.focus();
		return false;
	}

	if(input_hover_active_all)
		return;

	/* update the active element and show it */
	input_hover_active = this;
	input_hover_show(this.parentNode);
}

function input_hover_focusout_hdlr(e){
	/* hide the current element if it has not been modified */
	if(!input_hover_modified(this.parentNode)){
		input_hover_hide(this.parentNode);
		input_hover_active = null;
	}
}

/* callbacks for mouse events */
function input_hover_mouseenter_hdlr(e){
	/* show input-hover elements for a second element if the current
	 * one does not contain modifications */
	if(!input_hover_active_modified())
		input_hover_show(this);
}

function input_hover_mouseout_hdlr(e){
	/* hide input-hover element if the element is neither focused
	 * nor modified */
	if(!input_hover_modified(this) && !input_hover_focused(this))
		input_hover_hide(this);
}

/* callbacks for click events */
function input_hover_reset_click_hdlr(e){
	input_hover_reset(this.parentNode);
}

/* callbacls for form submission */
function input_hover_submit(e){
	e.preventDefault();

	/* get form reload configuration */
	var reload = false;

	if($(this).hasClass('input-hover-form-reload'))
		reload = true;

	if($(this).hasClass('input-hover-form-noreload'))
		reload = false;

	/* identify the submit action to trigger */
	// default is the form action
	var action = this.action;

	// check the currently focused element
	var active = document.activeElement;

	if(active.type == 'button'){
		// if the clicked button contains the 'formaction' property it is used
		if($(active).attr('formaction') && $(active).attr('formaction') != '')
			action = $(active).attr('formaction');
	}
	else{
		// if the current element is not a button, check if it has '-action-0' sibling
		var parent = active.parentNode;
		active = document.getElementById(parent.id + '-action-0');

		if(active != null){
			if($(active).attr('formaction') && $(active).attr('formaction') != '')
				action = $(active).attr('formaction');

			if(parent.className != 'input-hover-master')
				reload = true;
		}
	}

	/* trigger ajax processing */
	$.ajax({
		method: 'post',
		url: action,
		dataType: "text",
		data : $(this).serialize(),
		success: function(msg, status, data){
			if(reload){
				location.reload();
				return;
			}

			/* display status message */
			try{
				var resp = JSON.parse(msg);

				for(var i=0; i<resp.length; i++)
					statusbar_print(resp[i].type, resp[i].msg);
			}
			catch(e){
				statusbar_print('html', msg);
			}

			/* update all value if all elements have been active */
			if(input_hover_active_all){
				var masters = document.getElementsByClassName('input-hover-master');

				// update local storage and overlay with current value
				for(var i=0; i<masters.length; i++)
					input_hover_update(masters[i]);

				// hide all input elements
				input_hover_hide_all();

				return;
			}

			if(!input_hover_active)
				return;

			/* update the value of the input-hover elements */
			input_hover_update(input_hover_active.parentNode);

			/* hide the input-hover element */
			input_hover_hide(input_hover_active.parentNode);
	 	},
		error: function(xhr, desc, err){
			statusbar_print('error', err);
		}
	});
}

/**
 * \brief	register callbacks required for input-hover elements
 */
function input_hover_init(){
	/* register mouseover and mouseout events for master elements */
	masters = document.getElementsByClassName('input-hover-master');

	for(var i=0; i<masters.length; i++){
		masters[i].addEventListener('mouseover', input_hover_mouseenter_hdlr);
		masters[i].addEventListener('mouseout', input_hover_mouseout_hdlr);
	}

	/* register reset action for reset buttons */
	for(var i=0; i<masters.length; i++){
		btn = document.getElementById(masters[i].id + '-reset');

		if(btn != null)
			btn.addEventListener('click', input_hover_reset_click_hdlr);
	}

	/* hide button elements */
	buttons = document.getElementsByClassName("input-hover-button");

	for(var i=0; i<buttons.length; i++)
		buttons[i].style.visibility = 'hidden';

	/* register focusin and focus out events for input elements */
	inputs = document.getElementsByClassName("input-hover-input");

	for(var i=0; i<inputs.length; i++){
		inputs[i].addEventListener('focusin', input_hover_focusin_hdlr);
		inputs[i].addEventListener('focusout', input_hover_focusout_hdlr);
	}

	/* remove local storage entries for input values */
	for(var i=0; i<inputs.length; i++)
		rm(inputs[i].id + '_value');

	/* register submit handlers for input-hover forms */
	forms = document.getElementsByClassName("input-hover-form");

	for(var i=0; i<forms.length; i++){
		// skip forms who's action targets the current page
		if(forms[i].action == document.URL)
			continue;

		forms[i].addEventListener('submit', input_hover_submit);
	}
}


/* document change handler */
$(document).ready(input_hover_init);
$('body').on('user_event_body_changed', input_hover_init);

/* callbacks for show/reset-all buttons */
$('#input-hover-show-all').click(function(){input_hover_show_all()});
$('#input-hover-reset-all').click(function(){input_hover_hide_all()});
