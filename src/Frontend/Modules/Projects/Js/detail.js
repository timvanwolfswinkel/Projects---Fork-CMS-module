$(document).ready(function() {
	$(".colorbox").colorbox({
        maxWidth: '95%',
        maxHeight: '95%',
        current: jsData.projects.current,
        previous: jsData.projects.previous,
        next: jsData.projects.next,
        close: jsData.projects.close,
        xhrError: jsData.projects.xhrError,
        imgError: jsData.projects.imgError
    });
});
