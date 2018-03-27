/* onclick handler to toggle between readonly and read/write elements */
$('div.input-toggle').click(function(e) {
	/* get parent node */
	parent = this.parentNode;

	/* get readonly and modifyable elements */
	var ro = this;
	var rw = document.getElementById(this.id + "_rw");

	/* traverse to top 'input-toggle' node */
	while(parent.className == "input-toggle"){
		ro = parent;
		rw = document.getElementById(parent.id + "_rw");
		parent = parent.parentNode;
	}

	if(ro == null || rw == null)
		return;

	/* hide readonly element and show editable one */
	ro.style.display = "none";
	rw.style.display = "block";
});


function input_toggle_show(master){
	var input = document.getElementById(master.id + '-input');
	var commit = document.getElementById(master.id + '-commit');
	var cancel = document.getElementById(master.id + '-cancel');


	if(input != null){
		input.readOnly = false;
		input.style.borderColor = "black !important";

		if(get(input.id + '_value') == null){
			var value = input.value;

			if(input.type == "checkbox")
				value = input.checked;

			set(input.id + '_value', value);
		}
	}

	if(commit != null)
		commit.style.visibility = 'visible';

	if(cancel != null)
		cancel.style.visibility = 'visible';
}

function input_toggle_hide(master){
	var input = document.getElementById(master.id + '-input');
	var commit = document.getElementById(master.id + '-commit');
	var cancel = document.getElementById(master.id + '-cancel');


	if(input != null){
		input.readOnly = true;
		input.style.borderColor = "transparent !important";
	}

	if(commit != null)
		commit.style.visibility = 'hidden';

	if(cancel != null)
		cancel.style.visibility = 'hidden';
}

function input_toggle_modified(master){
	var input = document.getElementById(master.id + '-input');

	if(input == null)
		return false;

	var value = input.value;

	if(input.type == "checkbox")
		value = input.checked;

	return (get(input.id + '_value') != value.toString());
}

function input_toggle_focused(master){
	var input = document.getElementById(master.id + '-input');

	return (input != null && (document.activeElement == input));
}

$(document).ready(function(){
	/* register mouseover and mouseout events for master elements */
	masters = document.getElementsByClassName('input-toggle-master');

	for(var i=0; i<masters.length; i++){
		masters[i].addEventListener('mouseover', dynamic_input_mouseenter);
		masters[i].addEventListener('mouseout', dynamic_input_mouseout);
	}

	/* hide button elements */
	slaves = document.getElementsByClassName("input-toggle-button");

	for(var i=0; i<slaves.length; i++)
		slaves[i].style.visibility = 'hidden';

	/* register focusin and focus out events for input elements */
	inputs = document.getElementsByClassName("input-toggle-input");

	for(var i=0; i<inputs.length; i++){
		inputs[i].addEventListener('focusin', dynamic_input_focusin);
		inputs[i].addEventListener('focusout', dynamic_input_focusout);
	}

	/* remove local storage entries for input values */
	for(var i=0; i<inputs.length; i++)
		rm(inputs[i].id + '_value');
});

function dynamic_input_focusin(e){
	console.log("focusin " + this.id);
	console.log("data: " + get(this.id + '_value'));

	input_toggle_show(this.parentNode);
}

function dynamic_input_focusout(e){
	console.log("focusout " + this.id);

	if(!input_toggle_modified(this.parentNode))
		input_toggle_hide(this.parentNode);
}

function dynamic_input_mouseenter(e){
	console.log("mouseover " + this.id);

	input_toggle_show(this);
}

function dynamic_input_mouseout(e){
	console.log("mouseout " + this.id);

	console.log("modified: " + input_toggle_modified(this));
	console.log("focused: " + input_toggle_focused(this));

	if(input_toggle_modified(this) || input_toggle_focused(this))
		return;

	input_toggle_hide(this);
}
