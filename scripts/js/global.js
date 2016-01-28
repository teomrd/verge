/*--------------------------------------------Global Functions----------------------------------------------------*/
var lastRequestTimeStamp = new Date().getTime();
var page = 1;	
var pageSize = 100;
/***************************** router *************************/
var router = new kendo.Router();
router.route("/:mymode", function(mymode) {
	mode = mymode;
	clearSearchResults();
	$("#searchMode").html(mode);
	
	loadOnScrollMode = false;
	imageSizeSlider.enable(false);
	$("#loadmore").hide();
	
	clusterDS.read();
});
router.route("/:mode/:value", function(mode,value) {
	
	$("#searchMode").html(mode);
	$("#searchValue").html(value);
	clearSearchResults();
	$("#loadmore").hide();
	
	if(mode=="collection"){
		loadOnScrollMode = true;
		shotDS.query({
			filter: {
			    logic: "or",
			    filters: [
			      { field: "collection", operator: "eq", value: value },
			    ]
			},
			pageSize: 100,
			page: page
		});	
		ds = shotDS;
		$("#loadmore").fadeIn();
	}
	else if(mode=="search"){
		shotDS.query({
			filter: {
			    logic: "or",
			    filters: [
			      { field: "title", operator: "contains", value: $("#search").val() },
			    ]
			},
			pageSize: 200,
			page: page
		});
		ds = shotDS;
	}
	else if(mode=="year"){
		shotDS.query({
			filter: {
			    logic: "or",
			    filters: [
			      { field: "year", operator: "string_eq", value: value  },
			    ]
			},
			pageSize: 200,
			page: page
		});
		ds = shotDS;
	}
	else if(mode=="book"){
		shotDS.query({
			filter: {
			    logic: "or",
			    filters: [
			      { field: "book", operator: "string_eq", value: value  },
			    ]
			},
			pageSize: 500,
			sort: [
				{ field: "pageNumber", dir: "asc" },	
			],
			page: page
		});
		ds = shotDS;
	}
});
/***************************** router *************************/
/* youtube loading effect */
anim = 'la-anim-1';
animEl = document.querySelector( '.' + anim );
function loadEffect(sec){
	classie.add( animEl, 'la-animate' );
	$('.la-animate').css({
		'transition': 'transform '+sec+'s linear, opacity 1s '+sec+'s'
	});
	setTimeout(function(){ 
		//location.assign("./");
	}, sec*1000+2000);
}
/* youtube loading effect */

/* loading effect */
anim10 = 'la-anim-10';
animEl10 = document.querySelector( '.' + anim10 );

function loading(){
	$(".controls").fadeOut();
	imageSizeSlider.enable(true);
	isLoading = true;
	classie.add( animEl10, 'la-animate' );
}
function loaded(){
	isLoading = false;
	classie.remove( animEl10, 'la-animate' );
  
}
/* loading effect */

function clearSearchResults(){	
	loading();
	$("#search").html("");
	$("#shotsView").empty();
	page = 1;
}

$("body").on("click", ".burgerMenu", function(){
	$(this).toggleClass("active");
    $('.navigation').toggle('slide',{direction:'left'}, 200);
});

function judgeSelectedBar(){
	$("#selected").html( $("#selectedShotsView .selected").length );
	if( $("#selectedShotsView .selected").length>0 ){
		$(".complementaryBar").animate({
			height: "60px",
			top: "60px"
		});
		$(".main").animate({
			top: "60px"
		});
	}
	else{
		$("#selectedSwitcher").removeClass("selected");
		$("#selectedSwitcher").prop("checked",false);
		$("#selectedShotsView").hide();
		$("#shotsView").fadeIn();
		$("#loadmore").fadeIn();
				
		$(".complementaryBar").animate({
			height: "0px",
			top: "-60px"
		});
		$(".main").animate({
			top: "0px"
		});
	}
}


function detailView(collection, name){
	$('.info-cl').html("<div class='row loader'><img src='http://mklab-services.iti.gr/plugins/kendo/2014.3.1119/styles/MetroBlack/loading-image.gif'></div>");
	
	shotDetailDS = new kendo.data.DataSource({
	    transport: {
	    	read: {
	        	url: "api/"+apiVersion+"/iabi/shots",
	            type: "GET",
	            complete: function(e){
		            //console.log(e);
	            }
	        }
	    },
	    serverSorting: true,
	    serverFiltering: true,
	    filter: {
		    logic: "or",
		    filters: [
		      { field: "name", operator: "eq", value: name },
		    ]
		},
	    serverPaging: true,
	    pageSize: 1,
	    page:1,
	    schema: {
	    	total: "total",
	        data: "data",
	    },
	    requestEnd: function(e){
			loaded();
	  	},
	    change: function(e){
		    if(e.items.length>0){
				var detailViewTMPL = kendo.template($("#detailViewTMPL").html());
				$(".info-cl").html(detailViewTMPL({
					collection: e.items[0].collection,
					name: e.items[0].name['value'],
					title: e.items[0].title,
					book: e.items[0].book,
					year: e.items[0].year,
					pageNumber: e.items[0].pageNumber,
					imagesPath: '//mklab-services.iti.gr/Project_Images/flickr/images'
				}));   
		    }
			else{
				$(".info-cl").html("Image Metadata not found!");
			}

	    }
	});
	shotDetailDS.read();
}

function sesameOpen(openShot){
	if(openShot.hasClass('opened')){
		$('.container').children().removeClass('opened');
		$('.container').children().removeClass('edge');
		$('.info-bg').slideUp('fast');
	}
	else{
		$('.container').children().removeClass('opened');
		openShot.addClass('opened');
		detailView( $(".opened").children().first().data("collection") , $(".opened").children().first().data("name") );
	}
	var selectedPos = $('.opened').index("#"+openShot.parent().attr('id')+" .box")+1;
	var elementsInRow = Math.floor( openShot.parent().innerWidth() / $('.box').outerWidth() );
	var row = Math.ceil(selectedPos / elementsInRow);	
	var wrapPos = (row * elementsInRow);
	if (wrapPos > openShot.parent().children('.box').length){wrapPos = openShot.parent().children('.box').length;}
	
	edge = $('.edge').index('.box');
	$(".container").children().removeClass('edge');	    
	$( openShot.parent().children(".box")[wrapPos-1]).addClass('edge');	
	
	if (edge == $('.edge').index('.box') && $('.opened').index('.box')>0 ){
	    $('html, body').animate({
			scrollTop: $(".opened").offset().top-$('.top').height()-$('.complementaryBar').innerHeight()
		}, 200);
	}
	else{	
	    $('.info-bg').slideUp( function() { $(this).remove(); });
	    $('.edge').after('<div class="info-bg"><div class="previous"></div><div class="next"></div><div class="info-cl"><div class="row loader"><img src="http://mklab-services.iti.gr/plugins/kendo/2014.3.1119/styles/MetroBlack/loading-image.gif"></div></div></div>');
	    $('.info-bg').slideDown('fast');
	    if( openShot.parent().children().hasClass("opened") ){
			$('html, body').animate({
				scrollTop: $(".opened").offset().top-$('.top').height()-$('.info-bg').height()-$('.arrow').height()-$('.complementaryBar').innerHeight()
			}, 200);    
	    }
   }
}
/*--------------------------------------------Global Functions----------------------------------------------------*/