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
