/*--------------------------------------------OnDocumentLoad----------------------------------------------------*/
$(function() { 
//Start timers!!
loadEffect(time);
/***************************** popupNotification *************************/
popupNotification = $("#popupNotification").kendoNotification({
						position: {
							pinned: true,
							top: 30,
							right: 30
						},
						autoHideAfter: 5000,
						stacking: "down",
						templates: [{
                            type: "error",
                            template: $("#errorTemplate").html()
                        }, {
                            type: "success",
                            template: $("#successTemplate").html()
                        }]
					}).data("kendoNotification");
/***************************** popupNotification *************************/
	
	$('.countdownTime').countdown('destroy');
	$('.countdownTime').countdown({until: +time, layout: ' {mn} \' {sn}" '});
	
	imageSizeSlider = $("#imageSizeSlider").kendoSlider({
		increaseButtonTitle: "Right",
	    decreaseButtonTitle: "Left",
	    tooltip: {
		    enabled: false,
	    },
	    min: 1,
	    max: 10,
	    value: 5,
	    smallStep: 1,
	    largeStep: 1,
	    showButtons: false,
	    tickPlacement: "none",
	    change: function(e){
			$(".main .box").css({
			   'width': e.value+3+'vw',
			   'height': e.value+3+'vw' ,
			   'line-height': e.value+3+'vw' 
		    });
			if( $(".container").children().hasClass("opened") ){
				$(".opened img").click().click();//reopen
			}
			
	    },
	    slide: function(e){
			$(".main .box").css({
			   'width': e.value+3+'vw',
			   'height': e.value+3+'vw' ,
			   'line-height': e.value+3+'vw' 
		    });
	    }
	}).data("kendoSlider");
/********************************* Endless scrolling ******************************************/
$(document).on('scroll', onScroll);
function onScroll(event) {
	if( ($(window).scrollTop() + $(window).height() > $(document).height() - 10) && (lastRequestTimeStamp < new Date().getTime() - 2000) ) {
    	lastRequestTimeStamp = new Date().getTime();
        loading();
        ds.query({ 
	    	page: page++,
			pageSize: pageSize
	    });       
    }    
}
/********************************* Endless scrolling ******************************************/
/********************************* Selected Shots ********************************************/
$(".container").on( "click", ".box img" , function(event){
	sesameOpen( $(this).parent() );
});

$(".container").on( "click", ".box .col-cv-select" , function(e){
	e.preventDefault();
	if( $(this).parent().hasClass("selected") ){
		$(".shot"+$(this).parent().find("img").data("collection")+"_"+$(this).parent().find("img").data("name")).removeClass("selected");
		$("#selectedShotsView .shot"+$(this).parent().find("img").data("collection")+"_"+$(this).parent().find("img").data("name")).remove();
	}
	else{
		$(this).parent().addClass("selected").clone().appendTo( "#selectedShotsView" );
		$(".shot"+$(this).parent().find("img").data("collection")+"_"+$(this).parent().find("img").data("name")).addClass("selected");
	}
	judgeSelectedBar();
});

$("#selectedSwitcher").click(function(){
	$('.info-bg').slideUp('fast', function(){
		$('.container').children().removeClass('opened');
		$('.container').children().removeClass('edge');
	});
	if($("#selectedSwitcher").hasClass("selected")){
		$("#selectedSwitcher").removeClass("selected");
		$("#selectedShotsView").hide();
		$("#shotsView").fadeIn();

	}
	else{
		$("#selectedSwitcher").addClass("selected");
		$("#selectedShotsView").fadeIn();
		$("#shotsView").hide();

	}
});

$('.complementaryBar').on( "click", "#submit", function(){
	//do nothing for now;
});

$(".complementaryBar").on( "click", ".btn-danger", function(){
	$("#selectedShotsView").html("");	
	$("#selectedSwitcher").removeClass("selected");
	$("#selectedSwitcher").prop("checked",false);
	$("#selectedShotsView").hide();
	$("#shotsView").fadeIn();

	$(".container").children().removeClass('selected');
	judgeSelectedBar();
});
/********************************* Selected Shots ********************************************/
$("body").on( "click", ".searchByYear" , function(event){
	router.navigate("/year/"+$(this).data("year")); 
});

$("body").on( "click", ".searchByBook" , function(event){
	console.log( $(this).data("book") );
	router.navigate("/book/"+$(this).data("book")); 
});

$(".exit").click(function(){
	location.assign("./");
});

$(".logo").click(function(){
	location.reload();
});

$("body").on( "keypress", "#search",function(e){
if (e.keyCode == 13) {
	router.navigate("/search/"+$(this).val());   
}
});

router.start();
$(".row").on( "click", ".gotovideo" , function(event){
	router.navigate("/video/"+$(this).data("video"));
});
router.navigate("/collection/1");

});//on document load 
/*--------------------------------------------OnDocumentLoad----------------------------------------------------*/