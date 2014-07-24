/**
 * Create a formatted preview for the SOLR Mapping JSON
 * file or just goes to the rendered JSON
 * ---------------------------------------------------------
 * Author: Dion Snoeijen
 * Plugin: DS Solr
 */
function PreviewMapping() {

	// ------------------------- //
	//	Class variables          //
	// ------------------------- //

	this.$forms                 = null;
	this.$preview               = null;
	this.$pathsTable            = null;
	this.$submitRenderedPreview = null;
	this.$submitRealPreview	    = null;
	this.button 	            = null;

	this.RENDER  				= 'RENDER';
    this.REAL                   = 'REAL';

	// ------------------------- //
	//	Initialisation           //
	// ------------------------- //

	this.init = function() {
		// Select elements
		this.$forms = $('form');
		this.$pathsTable = $('#mapping-paths');
		this.$submitRenderedPreview = $('input[name=rendered_preview]');
		this.$submitRealPreview = $('input[name=real_preview]');

		// Set event listeners
		this.$submitRenderedPreview.on('click', $.proxy(this.onRenderedPreview, this));
		this.$submitRealPreview.on('click', $.proxy(this.onRealPreview, this));
		this.$forms.on('submit', $.proxy(this.onSubmit, this));
	}

	// ------------------------- //
	//	Event handlers           //
	// ------------------------- //

	this.onRenderedPreview = function(e) {
		this.button = this.RENDER;
	}

	this.onRealPreview = function(e) {
		this.button = this.REAL;
	}

	this.onSubmit = function(e) {
		e.preventDefault();

		// If preview is present remove it
		if (this.$preview !== null) {
			this.$preview.remove();
			this.$preview = null;
		}

		// Serialize the form values
		var $target = $(e.target);
		var formValues = $target.serialize();

        console.log(this.button);

		// So we can post them and catch the results
        if (this.button === this.RENDER) {
            $.post('', formValues).done($.proxy(this.dataLoaded, this));
        } else if (this.button === this.REAL) {
            $.post('', formValues).done($.proxy(this.dataLoadedReal, this));
        } else {
            console.log('ERROR');
        }
	}

    this.dataLoadedReal = function(data, textStatus, jqXHR) {

        this.$preview = $('<pre />')
            .attr('id', 'preview')
            .append(jqXHR.responseText);

        this.$pathsTable.after(this.$preview);
    }

	this.dataLoaded = function(data) {
		// Highlight the results
		var str = this.syntaxHighlight(data);

		// And create a preview container
		this.$preview = $('<pre />')
			.attr('id', 'preview')
			.append(str);

		// Add it below the table
		this.$pathsTable.after(this.$preview);
	}

	// ------------------------- //
	//	Methods                  //
	// ------------------------- //

	this.syntaxHighlight = function(json) {
	    if (typeof json != 'string') {
	         json = JSON.stringify(json, undefined, 2);
	    }
	    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
	        var cls = 'number';
	        if (/^"/.test(match)) {
	            if (/:$/.test(match)) {
	                cls = 'key';
	            } else {
	                cls = 'string';
	            }
	        } else if (/true|false/.test(match)) {
	            cls = 'boolean';
	        } else if (/null/.test(match)) {
	            cls = 'null';
	        }
	        return '<span class="' + cls + '">' + match + '</span>';
	    });
	}
}

var pm = new PreviewMapping().init();