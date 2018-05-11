function tab_init(){
	/* check if there is a previous tab to activate */
	// get page hash
	var tgt = window.location.hash.split('#')[1];

	// if page hash is empty check local storage
	if(tgt == null || tgt == '')
		tgt = get(window.location.pathname + 'tab_active');

	// show tab if defined
	if(tgt != null && tgt != ''){
		$(document.getElementById(tgt)).tab('show');

		// ensure to have 'tab_active' set on next reload
		set(window.location.pathname + 'tab_active', tgt);

		// clear hash -- prevent overwriting local storage
		// by hash values that are given through an url
		window.location.hash = '';
	}
}

function tab_click_hdlr(e){
	/* store selected tab */
	set(window.location.pathname + 'tab_active', this.id);

	/* activate tab */
	$(this).tab('show');

	/* hash change handler */
	window.addEventListener('hashchange', tab_init);
}


/* on-click handler for tabs */
$('.nav-tabs a').click(tab_click_hdlr);

/* initialisation */
$(document).ready(tab_init);
