/**
 * Index mappings into SOLR
 * ---------------------------------------------------------
 * Author: Dion Snoeijen
 * Plugin: DS Solr
 */
function IndexMapping() {

	// ------------------------- //
	// Class variables           //
	// ------------------------- //

	this.$form 			= null;
	this.$submitButton  = null;
	this.$table			= null;
	this.$checkAll		= null;
	this.postString 	= null;
	this.offset			= 5;
	this.postStringVars = [];
	this.sections 		= [];

	// ------------------------- //
	// Initialisation            //
	// ------------------------- //

	this.init = function(stepSize) {

		// Set params
		if (stepSize !== undefined) {
			this.offset = stepSize;
		}

		console.log(stepSize);

		// Select elements
		this.$form = $('form#index-form');
		this.$table = $('table#indexing');
		this.$submitButton = this.$form.find('input[type=submit]');
		this.$checkAll = $('td.thin a');

		// Set event listeners
		this.$form.on('submit', $.proxy(this.onSubmit, this));
		$(this).on('indexParamDataComplete', $.proxy(this.onIndexParamDataComplete, this));
		this.$checkAll.on('click', $.proxy(this.onCheckAllClick, this));
	}

	// ------------------------- //
	//	Event handlers           //
	// ------------------------- //

	this.onSubmit = function(e) {

		e.preventDefault();
		
		// Disable the submit button
		this.disableSubmitButton();

		// Grab the form
		var $target = $(e.target);

		// And set the post string that is to be modified
		this.postString = $target.serialize();
		this.disectPostString();
	}

	this.onIndexParamDataComplete = function() {

		var currentProcessingData = this.buildPostString();
		if (currentProcessingData !== false) {

			this.handleRowStatus(currentProcessingData);

			var jqxhr = $.post('', this.postString, $.proxy(function(data) {
				if (data.status === 1) {
					// Raise the passed cycles
					currentProcessingData.passedCycles++;
					// Recursion
					this.onIndexParamDataComplete();
				} else {
					// It did execute, but the returned data is unexpected. 
					// Check XHR response for more information.
					console.log('STEP FAILED');
				}
			}, this)).fail(function(data) {
				// The xhr request failed.
				console.log(data.responseText);
			});

		} else {
			// All steps have succeeded.
			this.handleRowStatus({'status':'complete'});
			this.enableSubmitButton();
		}
	}

	this.onCheckAllClick = function(e) {

		e.preventDefault();

		var checkBoxes = $('td.thin input[type=checkbox]');
		checkBoxes.prop("checked", !checkBoxes.prop("checked"));
	}

	// ------------------------- //
	// Methods                   //
	// ------------------------- //

	this.disableSubmitButton = function() {
		this.$submitButton.prop('disabled', true);
		this.$submitButton.addClass('disabled');
	}

	this.enableSubmitButton = function() {
		this.$submitButton.prop('disabled', false);
		this.$submitButton.removeClass('disabled');
	}

	/** 
	 * Provide visual feedback of the indexing status.
	 */
	this.handleRowStatus = function(data) {
		if (data.status === undefined) {
			this.animateProgress(data);
			if(data.passedCycles === 0) {
				this.deactivateRows();
				this.setRowToActive(data);
			}
		} else { 
			this.deactivateRows();
		}
	}

	this.animateProgress = function(data) {
		$progressBar = this.$table.find('tr#mapping-section-' + data.sectionId + '-' + data.locale).next().find('div');
		$progressBar.animate({width:String(100 * ((data.passedCycles + 1) / data.cycles)) + '%'});
	}

	this.setRowToActive = function(data) {
		$row = this.$table.find('tr#mapping-section-' + data.sectionId + '-' + data.locale);
		$row.css({'backgroundColor':'rgba(115, 127, 140, 0.05)'});
	}

	this.deactivateRows = function() {
		$rows = this.$table.find('tr');
		$rows.css({'backgroundColor':''});
	}

	/**
	 * This takes apart the initial post string to prepare for controlled indexing.
	 * It also brings in additional data needed for processing.
	 */
	this.disectPostString = function() {

		var decodedUrl = decodeURIComponent(this.postString).split('&');
		var numberPattern = /\d+/g;
		var letterPattern = /\w+/g;
		var expectedSections = 0;
		var count = 0;

		this.postStringVars = [];
		this.sections = [];

		// How many sections are we dealing with?
		for (var i in decodedUrl) {
			if (decodedUrl[i].indexOf('section') === 0) {
				expectedSections++;
			}
		}

		// Fetch data needed for indexing.
		for (var i in decodedUrl) {

			if (decodedUrl[i].indexOf('section') === 0) {

				var section 	= decodedUrl[i].split('=');
				var disect      = String(section[0].match(letterPattern)).split(',');
				var sectionId 	= parseInt(disect[1]);
				var locale  	= String(disect[2]);
				var url 		= String('/admin/dssolr/total/' + sectionId + '/' + locale);

				$.get(url, $.proxy(function(data) {

					data.total 	   	  = parseInt(data.total);
					data.sectionId 	  = parseInt(data.sectionId);
					data.locale       = String(data.locale);
					data.cycles	   	  = Math.ceil(data.total / this.offset);
					data.passedCycles = 0;

					this.sections.push(data);
					count++;

					// It seems we are ready to go!
					if (count === expectedSections) {
						$(this).trigger('indexParamDataComplete');
					}

				}, this));

			} else {

				this.postStringVars.push(decodedUrl[i]);

			}
		}

		this.postString = null;
	}

	/**
	 * This builds the current step post string and returns the data object it's built of.
	 * 
	 * @return Object
	 */
	this.buildPostString = function() {

		// Determine current status
		for (var i in this.sections) {

			// Take the current param's
			var data = this.sections[i];

			if (data.passedCycles < data.cycles)
			{
				this.postString = '';
				// Basic params
				this.postString = this.postStringVars.join('&');
				// Limit
				this.postString += '&limit=' + this.offset;
				// Offset
				this.postString += '&offset=' + (this.offset * data.passedCycles);
				// Section id
				this.postString += '&sectionId=' + data.sectionId;
				// Mapping path
				this.postString += '&mappingPath=' + data.mappingPath;
				// Locale
				this.postString += '&locale=' + data.locale;
				// Encode
				this.postString = encodeURIComponent(this.postString);
				this.postString = this.replaceAll('%3D', '=', this.postString);
				this.postString = this.replaceAll('%26', '&', this.postString);

				return data;
			}
		}

		this.postString = null;
		return false;
	}

	// ------------------------- //
	// Tools                     //
	// ------------------------- //

	this.replaceAll = function(find, replace, str) {
		return str.replace(new RegExp(find, 'g'), replace);
	}
}