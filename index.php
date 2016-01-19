<?php
	require 'configuration.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="VERGE | Video Search Engine">
    <meta name="author" content="mironidis@iti.gr">
    <link rel="icon" href="images/favicon.png">
    <title>VERGE | Video Search Engine </title>
    <script>
	    livesite = "http://mklab-services.iti.gr/verge/"; 
	    absolutePath = "<?php echo $absolutePath;?>";
	    apiVersion = "<?php echo $apiVersion; ?>";
	    time = "<?php echo $time; ?>";	 
	    time = time*60;//seconds!
    </script>
</head>

<body class="reveal">
	<!-- Primary Top Fixed Toolbar -->
	<div class="row top col-md-12">	
		<div class="col-xs-2 col-sm-1 col-md-1 fullHeight">
			<div class="burgerMenu">
				<a class="bt-menu-trigger"><span>Menu</span></a>
			</div>
		</div>
		<div class="col-xs-2 col-sm-1 col-md-1 fullHeight logo">
			<div class="verticalAlignMiddle">
				VERGE
			</div>
		</div>	
		
		<div class="col-xs-6 col-sm-3 col-md-6 fullHeight">
			<div class="halfHeight" style="width: 100%;">
				<div class="verticalAlignMiddle search">
			    	<input type="search" id="search" placeholder="Search..." />
				</div>
			</div>
			<div class="halfHeight">
				<div class="verticalAlignMiddle" align="left" >
				<label id="searchMode"></label> <span id="searchValue"></span>
				<label>Total: </label> <span id="total"></span>
				<label>Page: </label> <span id="page"></span>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-2 col-md-2 fullHeight">
			<div class="verticalAlignMiddle">
				<div class="imageSizeSlider">
					<input id="imageSizeSlider"/>             
				</div>
				<!--<i class="fa fa-picture-o" style="vertical-align: top;font-size: 2em;"></i> -->
			</div>
		</div>
		
		<div class="col-xs-1 col-sm-1 col-md-1 fullHeight countdown">
			<div class="verticalAlignMiddle">
				<i class="fa fa-clock-o"></i>
				<span class='countdownTime'></span>
			</div>
		</div>
		
		<div class="la-anim-1"></div><!-- YouTube loading effect is used for time indication -->
		<div class="la-anim-10"></div><!-- General purpose loader -->
		<div id="popupNotification"></div><!-- popupNotification DOM holder -->
	</div>
	<!-- Primary Top Fixed Toolbar -->
	<!-- Complementary Top Fixed Toolbar -->
	<nav class="complementaryBar navbar-default navbar-fixed-top">
	    <div class="col-md-2">
	      <a class="selectedLabel" href="#">Selected shots: <span id="selected"></span></a>
	    </div>	 
	    
	    <div class="col-md-1" style="height: 60px">
			<label class="verticalMiddle" ><input id="selectedSwitcher" type="checkbox" class="ios-switch green  bigswitch" /><div><div></div></div></label>
		</div>
		
	    <div class="col-md-5"></div>
	    
	    
	    <div class="col-md-2 countdown">
			<i class="fa fa-clock-o"></i><span class='countdownTime'></span>
		</div>
		   	
	      <div class="col-md-2 submitButtons">
	        <button id="submit" type="submit" class="btn btn-success">Submit</button>
	        <button style="margin-right: 5px;" class="btn btn-danger">Reset</button>
	      </div>
	    </div>
	</nav>
	<!-- Complementary Top Fixed Toolbar -->		
	<!-- Container emerges via Burger Menu  -->
	<div class="row col-md-2 navigation" >
		
	</div>
	<!-- Container emerges via Burger Menu  -->
	<!-- main view area  -->
	<div class="row main col-md-12">	
		<div class="container" id="selectedShotsView" ></div>
		<div class="container" id="shotsView" ></div>
	</div>
	<!-- main view area  -->
</body>
<script id="errorTemplate" type="text/x-kendo-template">
	<div class="wrong-pass">
		<img src="scripts/css/error-icon.png" />
    <h3>#= message #</h3>
    </div>
</script>

<script id="successTemplate" type="text/x-kendo-template">
    <div class="upload-success">
        <img src="scripts/css/success-icon.png" />
        <h3>#= message #</h3>
    </div>
</script>

<script type="text/x-kendo-tmpl" id="conceptItemTemplate">
	<div class="concept">
        #: name #
    </div>
</script>

<script type="text/x-kendo-tmpl" id="detailViewTMPL">
	<div class="col-xs-6 col-sm-6 col-md-6 detailsImage">
        <img src="#:imagesPath#/#: collection #/#: name #.jpg">
    </div>
    <div class="col-md-6 detailsMeta">
        <h3>#: title #</h3>
    	<p style="cursor: pointer;" class="searchByBook" data-book="#: book #">Book: #: book #</p>
    	<p>Year: <span style="cursor: pointer;" class="searchByYear" data-year="#: year #" >#: year #</span></p>
    	<p>Page No.: #: pageNumber #</p>
    </div>
    <div class="col-xs-6 col-sm-6 col-md-12" align="right">
	    <a href="https://www.flickr.com/photos/internetarchivebookimages/" target="_blank">"Internet Archive Book Images"</a> data collection form <a href="https://www.flickr.com" target="_blank"><img height="15px" src="https://s.yimg.com/pw/favicon.ico"> Flickr</a>
    </div>
</script>
   
<script type="text/x-kendo-template" id="shotTMPL">
    <div class="box file_image shot#: id #" style='width:#: imageSizeSlider.value()+3 #vw; height:#: imageSizeSlider.value()+3 #vw; line-height:#: imageSizeSlider.value()+3 #vw;' >
	    <img id="#: id #"  data-collection="#: collection #" data-name="#: name #" src="#:imagesPath#/#: collection #/#: name #.jpg">
		<div class="col-cv-select">
			<div class="col-list-checkbox"></div>
		</div>
		<div class="arrow"></div>
	</div>
</script>

<!-- Le CSS================================================== -->
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha256-k2/8zcNbxVIh5mnQ52A0r3a6jAgMGxFJFE2707UxGCk= sha512-ZV9KawG2Legkwp3nAlxLIVFudTauWuBpC10uEafMHYL0Sarrz5A7G79kXh5+5+woxQ5HM559XX2UZjMJ36Wplg==" crossorigin="anonymous">
<link href="http://mklab-services.iti.gr/plugins/kendo/2015.1.318.core/styles/kendo.common.min.css" rel="stylesheet">
<link id="style" href="http://mklab-services.iti.gr/plugins/kendo/2015.1.318.core/styles/kendo.material.min.css" rel="stylesheet">
<link href="scripts/css/style.css" type="text/css" rel="stylesheet"/>
<!--Le JavaScripts================================================== -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js" type="text/javascript"></script>
<script src="http://mklab-services.iti.gr/plugins/jquery/jquery.countdown.package-2.0.1/jquery.plugin.min.js" type="text/javascript"></script>
<script src="http://mklab-services.iti.gr/plugins/jquery/jquery.countdown.package-2.0.1/jquery.countdown.min.js" type="text/javascript"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha256-Sk3nkD6mLTMOF0EOpNtsIry+s1CsaqQC1rVLTAy+0yc= sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
<script src="http://mklab-services.iti.gr/plugins/kendo/2015.1.318.core/js/kendo.core.min.js" type="text/javascript"></script>
<script src="http://mklab-services.iti.gr/plugins/kendo/2015.1.318.core/js/kendo.ui.core.min.js" type="text/javascript"></script>
<script src="http://mklab-services.iti.gr/plugins/templates/CreativeLoadingEffects/js/classie.js"></script>
<script src="scripts/js/global.js" type="text/javascript"></script>
<script src="scripts/js/ds.js" type="text/javascript"></script>
<script src="scripts/js/ready.js" type="text/javascript"></script>
<html>