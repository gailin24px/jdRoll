/**
 * Control UI Notification Center.
 *
 * @package notification
 * @copyright (C) 2013 jdRoll
 * @license MIT
 */

var notifControllerImpl = function() {

	// Decremente count of notification number
	var decrementeNbMsg = function() {
	    var notifZone = $("#notifNumber");
	    var nbMsg = notifZone.text().trim();
	    nbMsg = nbMsg - 1;
	    notifZone.text(nbMsg);
	    if(nbMsg == 0) {
	        setToNoMsg();
	    }
	}

	// Clear notification zone
	var setToNoMsg = function() {
	    $("#notifNumber").text("0");
	    $("#notifIndicator").removeClass("btn-danger");
	    $("#msg-zone").text("");
	}

	// Ajax call to delete notification in database
	var ajaxDeleteNotif = function(idNotif) {
	    $.ajax({
	        type: "POST",
	        url: BASE_PATH + "/notification/del",
	            data: {id: idNotif},
	        success: function(msg){},
	        error: function(msg) {}
	    });
	}
	        

	return {

		// Delete specifique notification.
		deleteNotif : function(idNotif) {
		    ajaxDeleteNotif(idNotif);
		    decrementeNbMsg();
		},


		// Call all display notification
		clearNotif : function() {
		    $(".notifMsg").each(function(number, elt) {
		        idNotif = $(this).attr("id").replace("notif", "");
		        ajaxDeleteNotif(idNotif);
		    });
		    setToNoMsg();
		},


	    // Switch display of notification zone
		toggleNotif : function() {
		    if( $("#notif").hasClass("notifHide")) {
		        $("#notif").removeClass("notifHide");
		    } else {
		        $("#notif").addClass("notifHide");
		    }
		}
	}

}
var notifController = notifControllerImpl();