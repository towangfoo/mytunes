/**
 * Mytunes admin classes.
 * 
 *  1.) MytunesTransform
 *      Handles the transformation of Simple Products to Mytunes Products.
 *  
 *  2.) MytunesUpload
 *      Classes that handle flash uploader instances and their communication to the product edit page.
 *      Closely derived from Mage_Downloadable scripts.
 *  
 *  3.) MytunesTrackItems
 *      The bridge between the html snippet and the Javascript functions of the track list.
 *      Derived in parts from Mage_Downloadable scripts.
 */

MytunesTransform = Class.create();
MytunesTransform.prototype = {
    submitAction : null,
    btnHtmlId : null,
    initialize : function(btnHtmlId, config){
        this.submitAction = config.actionUrl;
        this.btnHtmlId = btnHtmlId;
    },
    submit : function(){
        // submit data to mytunes controller action
        theForm = $(this.btnHtmlId).up('form');
        theForm.action = this.submitAction;
        theForm.submit();
    }
}


var MytunesUpload = {
    uploadObj : $H({}),
    objCount : 0,

    // ****************
    // mass upload all configured track files
    massUpload : function(){
        thisObj = this;
        try {
            this.uploadObj.each(function(item){
                container = item.value.container.up('tr');
                if (container.visible() && !container.hasClassName('no-display')) {
                    item.value.upload();
                } else {
                    thisObj.unsetUploadObj(item.key);
                }
            });
        } catch (e) {
            try {
                console.log(e);
            } catch (e2) {
                alert(e.name + '\n' + e.message);
            }
        }
    }, // massUpload

    // ************
    // add an object to the list of uploads
    setUploadObj : function(key, obj){
        this.uploadObj.set(key, obj);
    }, // setUploadObj
    getUploadObj : function(key){
        try {
            return this.uploadObj.get(key);
        } catch (e) {
            alert(e.name + '\n' + e.message);
        }
    }, // getUploaderObj
    unsetUploadObj : function(key){
        try {
            this.uploadObj.unset(key);
        } catch (e) {
            alert(e.name + '\n' + e.message);
        }
    }, // unsetUploaderObj
}

MytunesUpload.FileUploader = Class.create();
MytunesUpload.FileUploader.prototype = {
    key : null,
    elmContainer : null,
    fileValueName : null,
    fileValue : null,
    idName : null,
    uploaderText: '<div class="no-display" id="[[idName]]-template">' +
        '<div id="{{id}}" class="file-row file-row-narrow">' +
            '<span class="file-info">' +
                '<span class="file-info-name">{{name}}</span>' +
                ' ' +
                '<span class="file-info-size">({{size}})</span>' +
            '</span>' +
            '<span class="progress-text"></span>' +
            '<div class="clear"></div>' +
        '</div>' +
    '</div>' +
    '<div class="no-display" id="[[idName]]-template-progress">' +
        '{{percent}}% {{uploaded}} / {{total}}' +
    '</div>',
    uploaderSyntax : /(^|.|\r|\n)(\[\[(\w+)\]\])/,
    uploaderObj : $H({}),
    config : null,
    initialize: function (key, elmContainer, fileValueName, fileValue ,idName, config, jPlayerConfig, translations) {
        this.key = key;
        this.elmContainer = elmContainer;
        this.fileValueName = fileValueName;
        this.fileValue = fileValue;
        this.idName = idName;
        this.config = config;
        uploaderTemplate = new Template(this.uploaderText, this.uploaderSyntax);
        Element.insert(
            elmContainer,
            {'top' : uploaderTemplate.evaluate({
                    'idName' : this.idName,
                    'fileValueName' : this.fileValueName,
                    'uploaderObj' : 'MytunesUpload.getUploadObj(\''+this.key+'\')'
                })
            }
        );
        if ($(this.idName+'_save')) {
            $(this.idName+'_save').value = this.fileValue.toJSON();
        }
        MytunesUpload.setUploadObj(
            this.key,
            new Flex.Uploader(this.idName, '/skin/adminhtml/default/default/media/uploaderSingle.swf', this.config)
        );
        if (varienGlobalEvents) {
            varienGlobalEvents.attachEventHandler('tabChangeBefore', MytunesUpload.getUploadObj(key).onContainerHideBefore);
        }
        new MytunesUpload.FileList(this.idName, MytunesUpload.getUploadObj(key), jPlayerConfig, translations);
    } // initialize
}

MytunesUpload.FileList = Class.create();
MytunesUpload.FileList.prototype = {
    file: [],
    containerId: '',
    container: null,
    uploader: null,
    fileListTemplate: '<span class="file-info">' +
        '<span class="file-info-name">{{name}}</span>' +
        ' ' +
        '<span class="file-info-size">({{size}})</span>' +
    '</span>' +
    '<div class="mytunes-audio-player">'+
        '<div id="jplayer_full_container_{{track_id}}" class="mytunes-jplayer-container"></div>' +
        '<p class="prelisten">{T{prelistenSample}}</p>' +
        '<a id="jplayer_full_{{track_id}}_control_play" class="play" href="#">play</a> ' +
        '<a id="jplayer_full_{{track_id}}_control_pause" class="pause" href="#">pause</a> ' +
        '<a id="jplayer_full_{{track_id}}_control_stop" class="stop" href="#">stop</a> ' +
    '</div>',
    templatePattern : /(^|.|\r|\n)({{(\w+)}})/,
    translateSyntax : /(^|.|\r|\n)({T{(\w+)}})/,
    listTemplate : null,
    jPlayerConfig : null,
    requestHash : {},

    initialize: function (containerId, uploader, jPlayerConfig, translations) {
        this.jPlayerConfig = jPlayerConfig;
        this.containerId  = containerId,
        this.container = $(this.containerId);
        this.uploader = uploader;
        this.uploader.onFilesComplete = this.handleUploadComplete.bind(this);
        this.file = this.getElement('save').value.evalJSON();
        
        this.fileListTemplate = this.translateTemplate(this.fileListTemplate, translations);
        this.listTemplate = new Template(this.fileListTemplate, this.templatePattern);
        this.updateFiles();
        this.uploader.onFileRemoveAll = this.handleFileRemoveAll.bind(this);
        this.uploader.onFileSelect = this.handleFileSelect.bind(this);
    },
    
    // ****************
    // translate template
    translateTemplate : function(templateStr, translations){
        var translated = new Template(templateStr, this.translateSyntax);
        return translated.evaluate(translations);
    }, // translateTemplate
    
    handleFileRemoveAll: function(fileId) {
        $(this.containerId+'-new').hide();
        $(this.containerId+'-old').show();
    },
    handleFileSelect: function() {
        $(this.containerId+'_type').checked = true;
    },
    getElement: function (name) {
        return $(this.containerId + '_' + name);
    },
    handleUploadComplete: function (files) {
        files.each(function(item) {
           if (!item.response.isJSON()) {
                try {
                    console.log(item.response);
                } catch (e2) {
                    alert(item.response);
                }
               return;
           }
           var response = item.response.evalJSON();
           if (response.error) {
               return;
           }
           var newFile = {};
           newFile.file = response.file;
           newFile.name = response.name;
           newFile.size = response.size;
           newFile.status = 'new';
           this.file[0] = newFile;
           this.uploader.removeFile(item.id);
        }.bind(this));
        this.updateFiles();
    },
    updateFiles: function() {
        this.getElement('save').value = this.file.toJSON();
        this.file.each(function(row){
            row.size = this.uploader.formatSize(row.size);
            Element.insert($(this.containerId + '-old'), {'bottom' : this.listTemplate.evaluate(row)});
            $(this.containerId + '-new').hide();
            $(this.containerId + '-old').show();
            
            // Register Click events for audio player
            this.initJPlayer('#jplayer_full_container_' + row.track_id, '#jplayer_full_' + row.track_id + '_control', row);
        }.bind(this));
    },
    // Prepare starting of Jplayer. Does not get started yet!
    // NOTE: needs jQuery available on the backend page!
    // @param String jQuery resource identifier for player instance
    // @param String jQuery resource identifier namespace for player controls
    // @param JSONObject track
    initJPlayer : function(playerContainer, controlNs, track) {
        jQuery(controlNs + '_pause').hide();
        baseUrl = this.jPlayerConfig.baseUrl;
        route = this.jPlayerConfig.sampleRoute;
        skuHash = this.base64encode(track.sku);
        
        // make request hash global for jPlayer scope - access to unique uri
        this.requestHash[track.track_id] = baseUrl + route + skuHash;
        var requestHash = this.requestHash;
        // init jPlayer
        jQuery(playerContainer).jPlayer({
            ready: function() {
                // FIXME!
                // it seems like we never get here with flash fallback
                // html/ogg works without problems ...
                jQuery(playerContainer).jPlayer("setMedia", {mp3: requestHash[track.track_id] + ".mp3", oga: requestHash[track.track_id] + ".ogg"});
            },
            swfPath: baseUrl + "js/mytunes/jplayer/",
            supplied:"mp3,oga",
            solution:"html,flash",
            preload: "none",
            //errorAlerts:true,  // enable debug messages from jPlayer
            //warningAlerts: true
        });
        // play button
        jQuery(controlNs + '_play').click(function(){
            jQuery(playerContainer).jPlayer("play");
            jQuery(controlNs + '_play').hide();
            jQuery(controlNs + '_pause').show();
            return false;
        });
        // pause button
        jQuery(controlNs + '_pause').click(function(){
            jQuery(playerContainer).jPlayer("pause");
            jQuery(controlNs + '_play').show();
            jQuery(controlNs + '_pause').hide();
            return false;
        });
        // stop button
        jQuery(controlNs + '_stop').click(function(){
            jQuery(playerContainer).jPlayer("stop");
            jQuery(controlNs + '_play').show();
            jQuery(controlNs + '_pause').hide();
            return false;
        });
    },
    
    /**
     * Base64 encode a string
     * 
     * @author Sebastian Althof <hello@mrfoo.de>
     * @see http://mrfoo.de/archiv/434-Base64-in-Javascript.html
     * 
     * @param string
     */
    base64encode : function(inp)
    {
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
}
var MytunesTrackItems = Class.create()
MytunesTrackItems.prototype = {
    templateText : '<tr>'+
        '<td>'+
            '<input type="hidden" name="mytunes[tracks][{{id}}][track_id]" value="{{track_id}}" />'+
            '<input type="hidden" name="mytunes[tracks][{{id}}][delete_track]" class="delete" value="" />'+
            '<input type="text" class="sort required-entry validate-zero-or-greater input-text" name="mytunes[tracks][{{id}}][track_number]" value="{{track_number}}" />'+
        '</td>'+
        '<td>'+
            '<input type="text" class="required-entry input-text" name="mytunes[tracks][{{id}}][trackname]" value="{{trackname}}" />'+
        '</td>'+
        '<td>'+
            '<select id="mytunes_tracks_{{id}}_single_download" class="select" name="mytunes[tracks][{{id}}][single_download]" style="width:4em">'+
                '<option value="1">{T{yes}}</option>'+
                '<option value="0">{T{no}}</option>'+
            '</select>'+
        '</td>'+
        '<td class="input-price">'+
            '<input type="text" class="required-entry validate-zero-or-greater input-text link-prices" name="mytunes[tracks][{{id}}][price]" value="{{price}}" />'+
            ' <label>[{T{currency}}]</label>'+
        '</td>'+
        '<td>'+
            '<input type="text" id="mytunes_tracks_{{id}}_downloads" name="mytunes[tracks][{{id}}][downloads]" class="sort input-text validate-zero-or-greater" value="{{downloads}}" />'+
            '<p><input type="checkbox" class="checkbox" id="mytunes_tracks_{{id}}_downloads_unlimited" name="mytunes[tracks][{{id}}][downloads_unlimited]" value="1" {{downloads_unlimited_checked}} /> <label for="mytunes_tracks_{{id}}_downloads_unlimited">{T{unlimited}}</label></p>'+
        '</td>'+
        '<td>'+
            '<div class="file">'+
                '<input type="hidden" class="validate-downloadable-file" id="mytunes_tracks_{{id}}_file_save" name="mytunes[tracks][{{id}}][file]" value="{{file_save}}" />'+
                '<div id="mytunes_tracks_{{id}}_file" class="uploader">'+
                    '<div id="mytunes_tracks_{{id}}_file-old" class="file-row-info"></div>'+
                    '<div id="mytunes_tracks_{{id}}_file-new" class="file-row-info new-file"></div>'+
                    '<div class="buttons">'+
                        '<div id="mytunes_tracks_{{id}}_file-install-flash" style="display:none">{T{flashRequired}}</div>'+
                    '</div>'+
                    '<div class="clear"></div>'+
                '</div>'+
            '</div>'+
            '<span id="mytunes_tracks_{{id}}_link_container"></span>'+
        '</td>'+
        '<td class="sample_settings">'+
         //   '<div class="current-sample">play current sample</div>'+
            '<p><input type="checkbox" class="checkbox" id="mytunes_tracks_{{id}}_create_sample" name="mytunes[tracks][{{id}}][create_sample]" value="1" {{create_sample_checked}} /> <label for="mytunes_tracks_{{id}}_create_sample">{T{create_sample}}</label></p>'+
            '<p><label for="mytunes_tracks_{{id}}_sample_start">{T{sample_start}}</label> <input type="text" id="mytunes_tracks_{{id}}_sample_start" name="mytunes[tracks][{{id}}][sample_start]" class="sort input-text" value="{{sample_start}}" /></p>'+
            '<p><label for="mytunes_tracks_{{id}}_sample_end">{T{sample_end}}</label> <input type="text" id="mytunes_tracks_{{id}}_sample_end" name="mytunes[tracks][{{id}}][sample_end]" class="sort input-text" value="{{sample_end}}" /></p>'+
        '</td>'+
        '<td>'+
            '<button type="button" class="scalable delete icon-btn"><span>{T{deleteItem}}</span></button>'+
        '</td>'+
    '</tr>',
    tbody : null,
    templateSyntax : /(^|.|\r|\n)({{(\w+)}})/,
    translateSyntax : /(^|.|\r|\n)({T{(\w+)}})/,
    itemCount : 0,
    fileUploaderConfig : null,
    jPlayerConfig : null,
    trackDefaults: null,
    translations : null,

    // *************
    // initialize tracks tools
    initialize : function(tbody, translations, defaultConfig) {
        if (!$(tbody)) {
            alert("mytunes tracks tbody id "+ tbody +" was not found in document. Can not show tracks.");
        }
        this.tbody = $(tbody);
        this.templateText = this.translateTemplate(translations);
        this.trackDefaults = defaultConfig;
        this.translations = translations;
    }, // initialize

    // *************
    // add a new (existing) track row to the table of tracks
    add : function(data) {
        alertAlreadyDisplayed = false;
        this.template = new Template(this.templateText, this.templateSyntax);

        if (!data.track_id) { // new track
            data = {};
            data.track_id  = '';
            data.downloads = this.trackDefaults.numDownloads;
            data.price = this.trackDefaults.trackPrice;
            data.single_download = 1;
            data.downloads_unlimited = this.trackDefaults.unlimitedDownloads;
            data.create_sample_checked = ' checked="checked"';
            data.sample_start = this.trackDefaults.sampleStart;
            data.sample_end = this.trackDefaults.sampleEnd;
        }
        else {
            if (!data.downloads) {
                data.downloads_unlimited = true;
            }
            data.create_sample_checked = '';
        }

        data.id = this.itemCount;

        if (data.downloads_unlimited == true) {
            data.downloads_unlimited_checked = ' checked="checked"';
        }

        Element.insert(this.tbody, {'bottom':this.template.evaluate(data)});
        
        if (data.track_id) {
            // existing track - hide sample settings
            $('mytunes_tracks_'+data.id+'_sample_start').up('p').hide();
            $('mytunes_tracks_'+data.id+'_sample_end').up('p').hide();
        }

        // toggle display of sample settings
        Event.observe($('mytunes_tracks_'+data.id+'_create_sample'), 'change', function(event) {
        	elm = Event.element(event);
        	if (elm.checked) {
        		$('mytunes_tracks_'+data.id+'_sample_start').up('p').show();
                $('mytunes_tracks_'+data.id+'_sample_end').up('p').show();
        	}
        	else {
        		$('mytunes_tracks_'+data.id+'_sample_start').up('p').hide();
                $('mytunes_tracks_'+data.id+'_sample_end').up('p').hide();
        	}
        });

        downloadsElm = $('mytunes_tracks_'+data.id+'_downloads');
        isUnlimitedElm = $('mytunes_tracks_'+data.id+'_downloads_unlimited');
        if (data.downloads_unlimited) {
            downloadsElm.disabled = true;
        }
        Event.observe(isUnlimitedElm, 'click', function(event){
            elm = Event.element(event);
            elm.up('td').down('input[type="text"].input-text').disabled = elm.checked;
        });

        singleDownloadElm = 'mytunes_tracks_'+data.id+'_single_download';
        if (data.single_download) {
            options = $(singleDownloadElm).options;
            for (var i=0; i < options.length; i++) {
                if (options[i].value == data.single_download) {
                    options[i].selected = true;
                }
            }
        }
        if (data.single_download == 0) {
            $(singleDownloadElm).up('tr').down('td.input-price .input-text').disabled = true;
        }
        Event.observe($(singleDownloadElm), 'change', function(event){
            elm = Event.element(event);
            sel = elm.selectedIndex;
            if ($(singleDownloadElm).options[sel].value == 0) {
                elm.up('tr').down('td.input-price .input-text').disabled = true;
            }
            else {
                elm.up('tr').down('td.input-price .input-text').disabled = false;
            }
        });

        if (!data.file_save) {
            data.file_save = [];
        }

        mytunesFile = $('mytunes_tracks_'+data.id+'_file');

        // audio file
        new MytunesUpload.FileUploader(
            'mytunes_upload_'+data.id,
            mytunesFile.up('td'),
            'mytunes[tracks]['+data.id+'][file]',
            data.file_save,
            'mytunes_tracks_'+data.id+'_file',
            this.fileUploaderConfig,
            this.jPlayerConfig,
            this.translations
        );

        $('mytunes_tracks_'+data.id+'_file_save').advaiceContainer = 'mytunes_tracks_'+data.id+'_link_container';

        this.itemCount++;
        this.bindRemoveButtons();
    }, // add

    // ********** set file uploader configuration
    setUploaderConfig : function(config){
        this.fileUploaderConfig = config;
    },
    
    // ********** set jPLayer config
    setJPlayerConfig : function(config) {
        this.jPlayerConfig = config;
    },

    // ******************
    // remove a row from the tracks list, make it ready to delete when form is submitted
    remove : function(event){
        var element = $(Event.findElement(event, 'tr'));
        alertAlreadyDisplayed = false;
        if(element){
            element.down('input[type="hidden"].delete').value = '1';
            Element.select(element, 'div.flex').each(function(elm){
                elm.remove();
            });
            element.addClassName('no-display');
            element.addClassName('ignore-validate');
            element.hide();
        }
    }, // remove

    // ****************
    // register remove buttons to their job
    bindRemoveButtons : function(){
        var buttons = $$('tbody#mytunes_tracklist_body .delete');
        for(var i=0;i<buttons.length;i++){
            if(!$(buttons[i]).binded){
                $(buttons[i]).binded = true;
                Event.observe(buttons[i], 'click', this.remove.bind(this));
            }
        }
    }, // bindRemoveButtons

    // ****************
    // translate template
    translateTemplate : function(translations){
        var translated = new Template(this.templateText, this.translateSyntax);
        return translated.evaluate(translations);
    }, // translateTemplate

    // ****************
    // mass upload all configured track files
    massUpload : function(){
        $('loading-mask').show();
        $('mytunes_btn_massupload').addClassName('disabled');
        MytunesUpload.massUpload();
        $('mytunes_btn_massupload').removeClassName('disabled');
        $('loading-mask').hide();
    }, // massUpload
    
    // *****************
    // create samples for all tracks that have the appropriate checkbox ticked
    createSamples : function(){
        $('loading-mask').show();
        $('mytunes_btn_createsamples').addClassName('disabled');
        alert("TODO: samples erzeugen (adminhtml.js :: 500) ...");
        $('mytunes_btn_createsamples').removeClassName('disabled');
        $('loading-mask').hide();
    }
}