function dragable_init(){
    $(".dragable").sortable();
    $(".dragable").disableSelection();
}

/* document change handler */
$(document).ready(dragable_init);
$('body').on('user_event_body_changed', dragable_init);
