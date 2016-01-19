/*-----------------------------------------------shotDS-------------------------------------------------*/
shotDS = new kendo.data.DataSource({
    transport: {
    	read: {
        	url: "api/"+apiVersion+"/iabi/shots",
            type: "GET",
            complete: function(e){
				console.log(e);
            }
        }
    },
    serverSorting: true,
    serverFiltering: true,
    serverPaging: true,
    pageSize: pageSize,
    page: page,
    schema: {
    	total: "total",
        data: "data",
        model: {
        	id: "id",
			fields: {
            	collection: {validation: { required: false}},
            	name: {validation: { required: false}}
        	}
        }
    },
    requestEnd: function(e){
		loaded();
  	},
    change: function(e){
	    var shotTMPL = kendo.template($("#shotTMPL").html());
		if(e.items.length==0){
            $("#popupNotification").kendoNotification().data("kendoNotification").show("There are no results for the searching criteria!", "info");
            $("#search").focus();
	    }
	    else{
	    	$("#total").html(e.sender._total);
	    	$("#page").html(page);
	  		$.each(e.items, function( index, value ) {
				$("#shotsView").append(shotTMPL({
					id: this.collection+"_"+this.name['value'],
					collection: this.collection,
					name: this.name['value'],
					imagesPath: '//mklab-services.iti.gr/Project_Images/flickr/images'
				}));
			});
	    }
    }
});

ds = shotDS;
/*-----------------------------------------------shotDS-------------------------------------------------*/