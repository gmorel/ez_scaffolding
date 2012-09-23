$(document).ready(function () {
    $('#slideshow-div').rsfSlideshow({
        controls: {
            playPause: {auto: true},
            previousSlide: {auto: true},
            nextSlide: {auto: true},
            index: {auto: true}
        }
    });
    
    $("#hint_button").click(function () {
        $("#sql_hint").toggle("slow");
    }); 
    
    $("#showAll").click(function () {
        $(".togglable").toggle("slow");
    }); 
    
    $("#showNew").click(function () {
        if ($('#new_table').is(':visible')) {
//            hideAll();
            $("#new_table").toggle("slow");
	}
	else {
//            showAll();
            $("#new_table").toggle("slow");
	}
    }); 
    
    $('#buildbtn').click(function(e) {
        if ($('#sql').val() == '') 
        {
            if($('#emptySql').length == 0)
            {
                $('<div>', {
                    'id': 'emptySql',
                    'class': 'message',
                    'text': 'Please add a MySQL table export in the field bellow.'
                }).insertBefore('#sql');
            }
            $('html').animate({scrollTop: 400},'slow'); 
            //$('#sql').effect('shake', {times:3}, 300);
            e.preventDefault();
        }
    });
});

    
    
var hiddenElements = new Array();

function selectAll(name) {
	var e = $(name);
//        console.log(e.value);
//	e.focus();
//	e.select();
//	try { // only works on ie
//		var r = e.createTextRange();
//		r.execCommand("Copy");
//	}
//	catch (e) {}


  window.prompt ("Copy to clipboard: Ctrl+C, Enter", e.value);

}

function showIt(name) {
		$(name).morph('height:400px; background: #eee; color: #CC0000;', { duration:0.3 } );
		hiddenElements.splice(hiddenElements.indexOf(name),1);
		$(name).style.overflow = 'auto';
}

function hideIt(name) {
		$(name).style.overflow = 'hidden';
		$(name).morph('height:10px; background: #fff; color: #DEB9A3;', { duration:0.3 } );
		hiddenElements[hiddenElements.length] = (name);
}


function toggle(name) {
	if ( hiddenElements.indexOf(name) == -1 ) {
		hideIt(name);
	}
	else {
		showIt(name);
	}
	
}




function showAll() {
	var nodes = document.getElementsByClassName('textarea');
	for (var i = 0; i < nodes.length; i++) {
		showIt(nodes[i].id);
	}	
}

function hideAll() {
	var nodes = document.getElementsByClassName('textarea');
	for (var i = 0; i < nodes.length; i++) {
		hideIt(nodes[i].id);
	}	
}		



