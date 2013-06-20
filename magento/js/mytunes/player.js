/*
 * Controls for embedded Mytunes Player.
 *
 * TODO: License
 *
 * @author      Steffen Mücke <mail@quellkunst.de
 * @contributor Sebastian Althof <hello@mrfoo.de> (base64 encode function)
 * @version     0.2.0
 * @date        2011-01-06
 */

/**
 * The Mytunes player object.
 */
function Mytunes() {
	this.config = {
		debugMode:       true, // TODO: set to false for production
		uniqueId:        undefined,
		parent:          null,
		baseUrl:         null,
		requestUri:      null,
		autoplay:        false,
		autoloop:        false,
		playPauseToggle: true,
		showPlaylist:    true,
		showPrev:        true,
		showNext:        true,
		showVolume:      true,
		volume:          80,
		startTrack:      1,
		tracks:          [],
		playerType:      'playlist',
		swfPath:         'js/mytunes/jplayer/', // relative to baseUrl
		buyComplete:     false,
		price:           undefined,
		addToCartUrl:    undefined,
		currency:        '€',
		inCart:          false,
		//
		// settings from here on are not configurable from the outside:
		labels: {
			addThisProductAsDownloadToCart: '<span class="new">New</span> Add this album as a downloadable product to the cart',
			downloadCompleteAlbum:          "Download complete Album",
			thisAlbumIsInCart:              "Album is in your cart.",
			thisTrackIsInCart:              "In your cart",
			addTrackToCart:                 "add track to cart",
			addAlbumToCart:                 "add album to cart",
		}
	};
	this.playlist = new Array();
	this.currentPos = 0;  // the track that is currently played
	this.mandatorySettings = ['baseUrl', 'requestUri', 'tracks', 'buyComplete'];
	this.optionalSettings = ['debugMode', 'autoplay', 'showPlaylist', 'showPrev', 'showNext',
	                         'showVolume', 'volume', 'startTrack', 'uniqueId', 'playerType', 
	                         'playPauseToggle', 'currency', 'price', 'addToCartUrl', 'inCart'];
}

/**
 * Initialize the player, creating all necessary markup.
 * 
 * @param string parent the div container where all the player stuff will be
 * @param JSONMap settings
 *   mandatory settings include: {
 *       baseUrl: '<base url of the host where the tracks are served>',
 *       requestUri: '<URI to the track controller>'
 *       tracks: <Array of tracks - see below for settings>
 *       buyComplete: <boolean> whether the whole album is available for download
 *   }
 *   optional settings include: {
 *       debugMode: <boolean>         # switch debug mode on/off - default: false
 *       autoplay: <boolean>          # switch autoplay on/off - default: false
 *       autoloop: <boolean>          # play all tracks in loop - default: false
 *       showPlaylist: <boolean>      # toggle display of a playlist of tracks - default: true
 *       showPrev: <boolean>          # toggle disply of next button - default: true
 *       showNext: <boolean>          # toggle display of previous button - default: true
 *       showVolume: <boolean>        # toggle display of volume control - default: true
 *       volume: <integer 0...100>    # setting of player volume (0 to 100) - default: 80
 *       startTrack: <integer>        # list position of the track to begin with - default: 1
 *       uniqueId: <string>           # a valid HTML id to identify the player instance - will be automatically created if not set
 *       playerType: <string>         # will be added to the player container div as a CSS class selector - default: undefined
 *       playPauseToggle: <boolean>   # when enabled, of the play and pause buttons only the inactive will be visible - default: true
 *       currency: <string>           # the symbol of the currency in use - default: €
 *       price: <float>               # the price for the complete album - required when buyComplete == true
 *       addToCartUrl: <string>       # the URL to add the album download to the cart - required when buyComplete == true
 *       inCart: <boolean>            # flag that shows the album as being in the cart - default: false
 *   }
 *
 *   A track has the following mandatory settings: {
 *       track:<integer>              # the track number within the whole album (not the index of the track in the list!)
 *       artist: <string>             # the artist name
 *       name: <string>               # the name of the track
 *       sku: <string>                # the Magento SKU
 *       buySingle <boolean>          # whether this track is salable as a single download
 *   }
 *   A track can have any of the following optional settings (some are required with certain combinations of other settings) {
 *       price: <float>               # the price for the track - required when buySingle == true
 *       addToCartUrl: <string>       # the URL to add the track download to the cart - required when buySingle == true
 *       inCart: <boolean>            # flag that shows the track as being in the cart (ignored when album is in the cart) - default: false
 *   }
 *   
 *   TODO: perform further input validation, check for dependencies between arguments
 */
Mytunes.prototype.init = function(parent, settings) {
	var thisObj = this;
	// set parent div
	if (parent == undefined || parent.length == 0 || jQuery("div#"+parent).length != 1) {
		this.triggerError('parent container <div id="' + parent + '" /> not found');
		return false;
	}
	this.config.parent = parent;
	// process mandatory settings
	jQuery.each(this.mandatorySettings, function(k,v) {
		if (settings[v] != undefined) {
			thisObj.setConfig(v, settings[v]);
		}
		else {
			thisObj.triggerError("mandatory setting missing: " + v);
			return false;
		}
	});
	// process optional settings
	jQuery.each(this.optionalSettings, function(k,v) {
		if (settings[v] != undefined) {
			thisObj.setConfig(v, settings[v]);
		}
	});
	
	// set needed state variables
	this.currentPos = this.config.startTrack;
	this.playlist = this.config.tracks;

	// create markup
	this.setUniqueId();
	this.createMarkup();
	
	return true;
}

/**
 * create all markup for the player
 */
Mytunes.prototype.createMarkup = function() {
	if (jQuery("#"+this.config.parent).length != 1) {
		return false;
	}
	/*
	 * The created markup should look like this:
	 * 
	 * <div class="mytunes_jplayer" id=".."></div>
	 * <ul class="mytunes_controls" id="..">
	 *   <li><a href="#" id=".." class="mytunes_controls_play" tabindex="1">play</a></li>
	 *   <li><a href="#" id=".." class="mytunes_controls_pause" tabindex="1">pause</a></li>
	 *   <li><a href="#" id=".." class="mytunes_controls_stop" tabindex="1">stop</a></li>
	 *   <li><a href="#" id=".." class="mytunes_controls_volmin" tabindex="1">min volume</a></li>
	 *   <li><a href="#" id=".." class="mytunes_controls_volmax" tabindex="1">max volume</a></li>
	 *   <li><a href="#" id=".." class="mytunes_controls_prev" tabindex="1">previous</a></li>
	 *   <li><a href="#" id=".." class="mytunes_controls_next" tabindex="1">next</a></li>
	 * </ul>
	 * <div class="mytunes_info" id="..">
	 *   <div class="mytunes_info_track" id=".."></div>
	 *   <div class="mytunes_info_play_time" id=".."></div>
	 *   <div class="mytunes_info_total_time" id=".."></div>
	 * </div>
	 * <div class="mytunes_progress" id="..">
	 *   <div class="mytunes_progress_load" id="..">
	 *     <div class="mytunes_progress_play" id=".."></div>
	 *   </div>
	 * </div>
	 * <div class="mytunes_volume_bar" id="..">
	 *   <div class="mytunes_volume_bar_value" id=".."></div>
	 * </div>
	 * <div class="mytunes_playlist" id="..">
	 *   <ul>
	 *     <li></li>
	 *   </ul>
	 * </div>
	 */
	var id = this.config.uniqueId;
	
	// add player type as css class selector if set
	if (this.config.playerType != '') {
		jQuery("#" + this.config.parent).addClass(this.config.playerType);
	}
	// markup for player container
	var player = '<div class="mytunes_jplayer" id="mytunes_jplayer_'+id+'"></div>';
	// markup for controls
	var controls = '<ul class="mytunes_controls" id="mytunes_controls_'+id+'">' +
		'<li><a href="#" id="mytunes_controls_play_'+id+'" class="mytunes_controls_play" tabindex="1">play</a></li>' +
		'<li><a href="#" id="mytunes_controls_pause_'+id+'" class="mytunes_controls_pause" tabindex="1">pause</a></li>' +
		'<li><a href="#" id="mytunes_controls_stop_'+id+'" class="mytunes_controls_stop" tabindex="1">stop</a></li>';
	if (this.config.showVolume)
		controls += '<li><a href="#" id="mytunes_controls_volmin_'+id+'" class="mytunes_controls_volmin" tabindex="1">min volume</a></li>';
	if (this.config.showVolume)
		controls += '<li><a href="#" id="mytunes_controls_volmax_'+id+'" class="mytunes_controls_volmax" tabindex="1">max volume</a></li>';
	if (this.config.showPrev)
		controls += '<li><a href="#" id="mytunes_controls_prev_'+id+'" class="mytunes_controls_prev" tabindex="1">previous</a></li>';
	if (this.config.showNext)
		controls += '<li><a href="#" id="mytunes_controls_next_'+id+'" class="mytunes_controls_next" tabindex="1">next</a></li>';
	controls += '</ul>';
	// markup for info
	var info = '<div class="mytunes_info" id="mytunes_info_'+id+'">' +
		'<div class="mytunes_info_track" id="mytunes_info_track_'+id+'"></div>' +
		'<div class="mytunes_info_play_time" id="mytunes_info_play_time_'+id+'"></div>' +
		'<div class="mytunes_info_total_time" id="mytunes_info_total_time_'+id+'"></div>' +
		'</div>';
	// markup for progress bar
	var progress = '<div class="mytunes_progress" id="mytunes_progress_'+id+'">' +
		'<div class="mytunes_progress_load" id="mytunes_progress_load_'+id+'">' +
		'<div class="mytunes_progress_play" id="mytunes_progress_play_'+id+'"></div>' +
		'</div>' +
		'</div>';
	// markup for volume bar
	var volumebar = '<div class="mytunes_volume_bar" id="mytunes_volume_bar_'+id+'">' +
		'<div class="mytunes_volume_bar_value" id="mytunes_volume_bar_value_'+id+'"></div>' +
		'</div>';
	// markup for playlist
	var playlist = '<div class="mytunes_playlist" id="mytunes_playlist_'+id+'">' +
		'<ul><li></li></ul>' +
		'</div>';

	// put it all together
	var htmlStr = player + controls + info + progress;
	if (this.config.showVolume)
		htmlStr += volumebar;
	if (this.config.showPlaylist)
		htmlStr += playlist;

	// append the markup
	jQuery("#"+this.config.parent).html(htmlStr);

	// create local reference copies for performance
	thisObj = this;
	var instRefPlayTime = jQuery("#mytunes_info_play_time_"+id);
	var instRefTotalTime = jQuery("#mytunes_info_total_time_"+id);

	// create callbacks: jPlayer
	jQuery("#mytunes_jplayer_"+id).jPlayer({
		ready: function() {
			thisObj.displayPlaylist();
			if(thisObj.config.autoplay) {
				thisObj.playlistChange(thisObj.currentPos);
			} else {
				thisObj.playlistConfig(thisObj.currentPos);
			}
		},
		swfPath: thisObj.config.baseUrl + thisObj.config.swfPath,
		supplied:"mp3,oga",
		solution:"html,flash",
		preload: "none",
		customCssIds: true,
		// errorAlerts:true,  // enable debug messages from jPlayer
		// warningAlerts:true,
		// nativeSupport:false // for flash testing
		cssSelectorAncestor: "",
		cssSelector: {
			'play': "#mytunes_controls_play_"+id,
			'pause': "#mytunes_controls_pause_"+id,
			'stop': "#mytunes_controls_stop_"+id,
			'seekBar': "#mytunes_progress_load_"+id,
			'playBar': "#mytunes_progress_play_"+id,
			'mute': "#mytunes_controls_volmin_"+id,
			'unmute': "#mytunes_controls_volmax_"+id,
			'volumeBar': "#mytunes_volume_bar_"+id,
			'volumeBarValue': "#mytunes_volume_bar_value_"+id,
			'currentTime': "#mytunes_info_play_time_"+id,
			'duration': "#mytunes_info_total_time_"+id
		},
		ended: function() {
			thisObj.playNext(this.config.autoloop);
		}
	});
	// callback: prev button
	if (this.config.showPrev) {
		jQuery("#mytunes_controls_prev_"+id).click( function() {
			thisObj.playPrev();
			jQuery(this).blur();
			return false;
		});
	}
	// callback: next button
	if (this.config.showNext) {
		jQuery("#mytunes_controls_next_"+id).click( function() {
			thisObj.playNext(true);
			jQuery(this).blur();
			return false;
		});
	}
	// callback: play button
	jQuery("#mytunes_controls_play_"+id).click( function() {
		thisObj.play();
		jQuery(this).blur();
		return false;
	});
	// callback: pause button
	jQuery("#mytunes_controls_pause_"+id).click( function() {
		thisObj.pause();
		jQuery(this).blur();
		return false;
	});
	// callback: stop button
	jQuery("#mytunes_controls_stop_"+id).click( function() {
		thisObj.stop();
		jQuery(this).blur();
		return false;
	});
	// callbacks: min and max volume
	if (this.config.showVolume) {
		jQuery("#mytunes_controls_volmin_"+id).click( function() {
			thisObj.setVolume(0);
			jQuery(this).blur();
			return false;
		});
		jQuery("#mytunes_controls_volmax_"+id).click( function() {
			thisObj.setVolume(100);
			jQuery(this).blur();
			return false;
		});
	}
	
	// hide pause button when the toggling of play and pause is enabled
	if (this.config.playPauseToggle) {
		jQuery("#mytunes_controls_pause_"+id).hide();
	}
	return true;
}

/**
 * Create the markup to show playlist items.
 */
Mytunes.prototype.displayPlaylist = function() {
	if (this.config.showPlaylist) {
		var id = this.config.uniqueId;
		var thisObj = this;
		jQuery("#mytunes_playlist_"+id+" ul").empty();
		for (i=1; i <= this.playlist.length; i++) {
			var listItem = (i == this.playlist.length) ? "<li class='mytunes_playlist_item_last'>" : "<li>";
			// default track information
			listItem += this.playlist[i-1].track + " - <a href='#' id='mytunes_playlist_"+id+"_item_"+i+"' tabindex='1'>" + this.playlist[i-1].name + "</a> by " + this.playlist[i-1].artist;
			listItem += '<span class="mytunes_playlist_item_options">'
			// add download-track to cart
			if (this.playlist[i-1].buySingle == true) {
				if (this.config.inCart == false) {
					// show the following only when the whole album is not in the cart
					if (this.playlist[i-1].inCart == true) {
						listItem += '<span class="message incart">' + this.config.labels.thisTrackIsInCart + '</span>'
						listItem += '<span class="singleprice incart">' + this.formatPrice(this.playlist[i-1].price) + '</span>'
					} else {
						listItem += '<span class="singleprice">' + this.formatPrice(this.playlist[i-1].price) + '</span>'
						listItem += '<a href="#" class="tracktocart" id="tracktocart_'+i+'" title="' + this.config.labels.addTrackToCart + '">' + this.config.labels.addTrackToCart + '</a>'
					}
				}
			}
			listItem += "</span></li>";
			jQuery("#mytunes_playlist_"+id+" ul").append(listItem);
			// callback for playlist item
			jQuery("#mytunes_playlist_"+id+"_item_"+i).data( "index", i ).click( function() {
				var index = jQuery(this).data("index");
				if (thisObj.currentPos != index) {
					thisObj.playlistChange( index );
				} else {
					thisObj.play();
				}
				jQuery(this).blur();
				return false;
			});
			// callback for addToCart
			if (this.playlist[i-1].inCart == false && this.playlist[i-1].buySingle == true) {
				if (this.playlist[i-1]['addToCartUrl'] == undefined) {
					this.triggerError('missing track parameter for track #'+ (i-1) +': addToCartUrl');
				}
				else {
					jQuery("#mytunes_playlist_"+id+" #tracktocart_"+i).data("request", this.playlist[i-1].addToCartUrl ).click(function(){
						thisObj.addToCart(jQuery(this).data("request"));
						return false;
					});
				}
			}
			// style additions when track is in cart
			if (this.config.inCart == true || this.playlist[i-1].inCart == true) {
				jQuery("#mytunes_playlist_"+id+" ul li:last").addClass("incart");
			}
		}
		// add complete album to cart
		if (this.config.buyComplete == true) {
			if (this.config.inCart == true) {
				var listItem = "<li class='mytunes_playlist_item_downloadAlbum incart'>";
				listItem += this.config.labels.downloadCompleteAlbum
				listItem += '<span class="mytunes_playlist_item_options">'
				listItem += '<span class="message incart">' + this.config.labels.thisAlbumIsInCart + '</span>'
				listItem += '<span class="totalprice incart">' + this.formatPrice(this.config.price) + '</span>'
				listItem += '</span></li>';
				jQuery("#mytunes_playlist_"+id+" ul").append(listItem);
			} else {
				var listItem = "<li class='mytunes_playlist_item_downloadAlbum'>";
				listItem += this.config.labels.addThisProductAsDownloadToCart;
				listItem += '<span class="mytunes_playlist_item_options">'
				listItem += '<span class="totalprice">' + this.formatPrice(this.config.price) + '</span>'
				listItem += '<a href="#" class="albumtocart" title="' + this.config.labels.addAlbumToCart + '">' + this.config.labels.addAlbumToCart + '</a>'
				listItem += '</span></li>';
				jQuery("#mytunes_playlist_"+id+" ul").append(listItem);
				// callback for addToCart
				jQuery(".mytunes_playlist_item_downloadAlbum .albumtocart").click(function(){
					thisObj.addToCart(thisObj.config.addToCartUrl);
					return false;
				});
			}
		}
	}
}

/**
 * change the item to play and play it.
 * 
 * @param int index item to play
 */
Mytunes.prototype.playlistChange = function(index) {
	var id = this.config.uniqueId;
	this.playlistConfig(index);
	this.play();
}

/**
 * alter playlist according to selected track.
 * 
 * @param int index selected item
 */
Mytunes.prototype.playlistConfig = function(index) {
	var id = this.config.uniqueId;
	jQuery("#mytunes_playlist_"+id+"_item_"+this.currentPos).removeClass("mytunes_playlist_current").parent().removeClass("mytunes_playlist_current");
	jQuery("#mytunes_playlist_"+id+"_item_"+index).addClass("mytunes_playlist_current").parent().addClass("mytunes_playlist_current");
	this.currentPos = index;
	// specify the filename to use to get the audio file
	var skuHash = this.base64encode(this.playlist[index-1].sku);
	var request = this.config.baseUrl + this.config.requestUri + skuHash;
	jQuery("#mytunes_jplayer_"+id).jPlayer("setMedia", { mp3: request+".mp3", oga: request+".ogg" });
}

/**
 * play the current track.
 */
Mytunes.prototype.play = function() {
	var id = this.config.uniqueId;
	// toggle control visibility
	if (this.config.playPauseToggle) {
		jQuery("#mytunes_controls_pause_"+id).show();
		jQuery("#mytunes_controls_play_"+id).hide();
	}
	jQuery("#mytunes_jplayer_"+id).jPlayer("play");
}

/**
 * stop the playing of the current track.
 */
Mytunes.prototype.stop = function() {
	var id = this.config.uniqueId;
	jQuery("#mytunes_jplayer_"+id).jPlayer("stop");
}

/**
 * pause the playing of the current track.
 */
Mytunes.prototype.pause = function() {
	var id = this.config.uniqueId;
	// toggle control visibility
	if (this.config.playPauseToggle) {
		jQuery("#mytunes_controls_play_"+id).show();
		jQuery("#mytunes_controls_pause_"+id).hide();
	}
	jQuery("#mytunes_jplayer_"+id).jPlayer("pause");
}

/**
 * play the next on the playlist.
 * 
 * @param boolean loop at the end of the playlist
 * Passed as a parameter, as the next button may loop the playlist even if autoloop is disabled.
 * 
 */
Mytunes.prototype.playNext = function(loop) {
	var index = (this.currentPos < this.playlist.length) ? this.currentPos+1 : 1;
	if (loop || (this.currentPos != this.playlist.length))
		this.playlistChange(index);
}

/**
 * play the previous track on the playlist.
 */
Mytunes.prototype.playPrev = function() {
	var index = (this.currentPos > 1) ? this.currentPos-1 : this.playlist.length;
	this.playlistChange(index);
}

/**
 * set the volume.
 * 
 * @param int 0..100 the volume
 */
Mytunes.prototype.setVolume = function(vol) {
	var volume = (vol > 100) ? 100 : (vol < 0 ? 0 : vol);
	var id = this.config.uniqueId;
	jQuery("#mytunes_jplayer_"+id).jPlayer("volume", volume);
}

/**
 * return a formatted price string including currency symbol.
 * 
 * @param float the price
 * 
 * @return string
 */
Mytunes.prototype.formatPrice = function(float) {
	var str = '';
	var price = parseFloat(float);
	
	if(price<0){
		str+= '-';
	}
	str+= (Math.round(price*100)/100).toString();
	if (this.config.currency)
		str+= "&nbsp;" + this.config.currency;
	return str;
}

/**
 * add a product to the cart via a GET request
 * 
 * @param string the request URL
 */
Mytunes.prototype.addToCart = function(request) {
	if (request != undefined && request.length > this.config.baseUrl.length) {
		window.location = request;
	}
}

/**
 * create a unique ID for the player, if no id was passed with the settings.
 */
Mytunes.prototype.setUniqueId = function() {
	if (this.config.uniqueId == undefined) {
		var date = new Date();
		this.config.uniqueId = date.getTime().toString(16).substr(-6);
	}
}


/**
 * set a config variable.
 * 
 * @param string key
 * @param mixed value
 */
Mytunes.prototype.setConfig = function(k, v) {
	this.config[k] = v;
}

/**
 * Show an error when in debug mode.
 * 
 * @param string message
 */
Mytunes.prototype.triggerError = function(msg) {
	if (this.config.debugMode == true) {
		alert('[Mytunes Error] ' + msg);
	}
}

/**
 * Base64 encode a string
 * 
 * @author Sebastian Althof <hello@mrfoo.de>
 * @see http://mrfoo.de/archiv/434-Base64-in-Javascript.html
 * 
 * @param string
 */
Mytunes.prototype.base64encode = function(inp) {
	var key="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var chr1,chr2,chr3,enc3,enc4,i=0,out="";
	while(i<inp.length){
		chr1=inp.charCodeAt(i++);if(chr1>127) chr1=88;
		chr2=inp.charCodeAt(i++);if(chr2>127) chr2=88;
		chr3=inp.charCodeAt(i++);if(chr3>127) chr3=88;
		if(isNaN(chr3)) {enc4=64;chr3=0;} else enc4=chr3&63;
		if(isNaN(chr2)) {enc3=64;chr2=0;} else enc3=((chr2<<2)|(chr3>>6))&63;
		out+=key.charAt((chr1>>2)&63)+key.charAt(((chr1<<4)|(chr2>>4))&63)+key.charAt(enc3)+key.charAt(enc4);
	}
	return encodeURIComponent(out);
}