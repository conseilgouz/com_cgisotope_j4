/**
* CG Isotope Component  - Joomla 4.0.0 Component 
* Version			: 2.3.3
* Package			: CG ISotope
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
/* 
* ---------->          VueJs version <----------------------
*/

if (typeof Joomla === 'undefined' || typeof Joomla.getOptions === 'undefined') {
	return false;
} else {
	var options = Joomla.getOptions('cg_isotope_'+myid');
}

var zoom = new Vue({
	el: '#isotope-div',
	created() {
		myid = this.$attrs['data'];
		if (options.entree == "k2") {
			iso_cat_k2(myid,options); 
		} else { 
			if (options.article_cat_tag == "fields") {
				iso_fields(myid,options);
			} else {
				iso_cat_k2(myid,options);
			}
		}
	},
	data: {
	},
	currentLayout: 'masonry',
	selected: null,
	sortOption: options.sortby,
	filterOption: "show all",
    option: {
      itemSelector: ".element-item",
      getFilterData: {
        "show all": function() {
          return true;
        },
	  },
	},
	methods: {
		sort: function () {
			var sortValue = this.$attrs['data-sort-value'],
			sens = this.$attrs['data-sens'];
			sortValue = sortValue.split(',');
			if (sens == "+") {
				this.$attr["data-sens"] = "-";
				asc = true;
			} else {
				this.$attr["data-sens"] = "+";
				asc = false;
			}
			this.isotopeSort(sortValue); 
			this.sortOption(asc);
		};
	}
})

function iso_cat_k2 (myid,options) {
	var me = "#isotope-main-"+myid+" ";
	var qsRegex;
	var parent = 'cat';
	var items_limit = options.limit_items;
	var sav_limit = options.limit_items;	
	var filters = {};
	if (options.limit_items == 0) { // no limit : hide show more button
		jQuery(me+'.iso_button_more').hide();
	}
	if (options.default_cat == "") 
		filters['cat'] = ['*']
	else 
		filters['cat'] = [options.default_cat];
	if (options.default_tag == "") 
		filters['tags'] = ['*']
	else 
		filters['tags'] = [options.default_tag];
	
	if ((options.layout == "masonry") || (options.layout == "fitRows") || (options.layout == "packery"))
		jQuery('#isotope-main-' + myid + ' .isotope_item').css("width", (100 / parseInt(options.nbcol)-2)+"%" );
	if (options.layout == "vertical") 
		jQuery('#isotope-main-' + myid + ' .isotope_item').css("width", "100%" );
	jQuery('#isotope-main-' + myid + ' .isotope_item').css("background", options.background );
	if (parseInt(options.imgmaxheight) > 0) 
		jQuery('#isotope-main-' + myid + ' .isotope_item img').css("{max-height",options.imgmaxheight + "px");
	if (parseInt(options.imgmaxwidth) > 0) 
		jQuery('#isotope-main-' + myid + ' .isotope_item img').css("{max-height",options.imgmaxwidth + "px");
	var $grid = jQuery(me + '.isotope_grid').imagesLoaded(function() {
		$grid.isotope({ 
			itemSelector: me + '.isotope_item',
			percentPosition: true,
			layoutMode: options.layout,
			getSortData: {
				title: '[data-title]',
				category: '[data-category]',
				date: '[data-date]',
				click: '[data-click] parseInt',
				rating: '[data-rating] parseInt',
				id: '[data-id] parseInt'
			},
			sortBy: options.sortby,
			sortAscending: (options.ascending == "true"),
			filter: function() {
				var $this = jQuery(this);
				var searchResult = qsRegex ? $this.text().match( qsRegex ) : true;
				var	lacat = $this.attr('data-category');
				var laclasse = $this.attr('class');
				var lescles = laclasse.split(" ");
				var buttonResult = false;
				if ((filters['cat'].indexOf('*') != -1) && (filters['tags'].indexOf('*') != -1)) { return searchResult && true};
				count = 0;
				if (filters['cat'].indexOf('*') == -1) { // on a demandé une classe
					if (filters['cat'].indexOf(lacat) == -1)  {
						return false; // n'appartient pas à la bonne classe: on ignore
					} else { count = 1; } // on a trouvé la catégorie
				}
				if (filters['tags'].indexOf('*') != -1) { // tous les tags
					return searchResult && true;
				}
				for (var i in lescles) {
					if  (filters['tags'].indexOf(lescles[i]) != -1) {
						buttonResult = true;
						count += 1;
					}
				}
				if (options.searchmultiex == "true")	{
					lgth = filters['cat'].length + filters['tags'].length;
					if ((filters['tags'].indexOf('*') != -1) || (filters['cat'].indexOf('*') != -1)) {lgth = lgth - 1;}
					return searchResult && (count == lgth);
				} else { 
					return searchResult && buttonResult;
				}
			} // end of filter
		}); // end of grid
		updateFilterCounts();
	}); // end of imageloaded
	jQuery(me + '.isotope-div').on("refresh", function(){
 	  $grid.isotope();
	});

	jQuery(me+'.sort-by-button-group').on( 'click', 'button', function() {
		var sortValue = jQuery(this).attr('data-sort-value'),
		sens = jQuery(this).attr('data-sens');
		sortValue = sortValue.split(',');
		if (sens == "+") {
			jQuery(this).attr("data-sens","-");
			asc = true;
		} else {
			jQuery(this).attr("data-sens","+");
			asc = false;
		}
		$grid.isotope({ 
			sortBy: sortValue, 
			sortAscending: asc,
		});
	});
	jQuery(me+'.sort-by-button-group').each( function( i, buttonGroup ) {
		var $buttonGroup = jQuery( buttonGroup );
		$buttonGroup.on( 'click', 'button', function() {
			$buttonGroup.find('.is-checked').removeClass('is-checked');
			jQuery( this ).addClass('is-checked');
		});
	});
	
// use value of search field to filter
	var $quicksearch = jQuery(me+'.quicksearch').keyup( 
		debounce( function() {
			qsRegex = new RegExp( $quicksearch.val(), 'gi' );
			$grid.isotope();
			updateFilterCounts();
		}) 
	);
// 2.3.3 : clear serach button
	jQuery(me+'.ison-cancel-squared').on( 'click', function() {
		jQuery(me+'.quicksearch').val("");
		qsRegex = new RegExp( $quicksearch.val(), 'gi' );
		$grid.isotope();
		updateFilterCounts();
		jQuery(me+'.quicksearch').focus();
	});
	if  (options.displayfilter == "list") { 
		jQuery(me+'.filter-button-group-tags').on( 'change', function() {
			parent = jQuery(this).attr('data-filter-group');
			var sortValue = jQuery(this).find(":selected").val();
			if (sortValue == options.liball)   {
				filters[parent] = ['*'];
			} else { 
				filters[parent] = [sortValue];
			}
			$grid.isotope(); 
			updateFilterCounts();
		});
	} 
	if  (options.displayfiltercat == "list") { 
		jQuery(me+'.filter-button-group-cat').on( 'change', function() {
			parent = jQuery(this).attr('data-filter-group');
			var sortValue = jQuery(this).find(":selected").val();
			if (sortValue == options.liball)   {
				filters[parent] = ['*'];
			} else { 
				filters[parent] = [sortValue];
			}
			$grid.isotope(); 
			updateFilterCounts();
		});
	} 
	if ((options.displayfiltercat == "multi") || (options.displayfiltercat == "multiex")) {
		jQuery(me+'.filter-button-group-cat').on( 'click', 'button', function() {
			parent = jQuery(this).parent().attr('data-filter-group');
			sortValue = jQuery(this).attr('data-sort-value');
			jQuery(this).toggleClass('is-checked');
			var isChecked = jQuery(this).hasClass('is-checked');
			if (sortValue == 0) { // tout
				filters[parent] = ['*'];
			} else { 
				removeFilter(filters, parent,'*');
				if ( isChecked ) {
					addFilter( filters, parent,sortValue );
				} else {
					removeFilter( filters, parent, sortValue );
				}	
			}
			$grid.isotope();
			updateFilterCounts();
		});
		jQuery(me+'.filter-button-group-cat').each( function( i, buttonGroup ) {
			var $buttonGroup = jQuery( buttonGroup );
			$buttonGroup.on( 'click', 'button', function() {
				if (jQuery(this).attr('data-sort-value') == 0) { // on a cliqué sur tout => on remet le reste à blanc
					jQuery(this).parent().find('.is-checked').removeClass('is-checked');
					jQuery( this ).addClass('is-checked');
				} else { // on a cliqué sur un autre bouton : uncheck le bouton tout
					jQuery(this).parent().find('[data-sort-value="0"]').removeClass('is-checked');
				}
			});
		});
	}
	if ((options.displayfilter == "multi") || (options.displayfilter == "multiex")) { 
		jQuery(me+'.filter-button-group-tags').on( 'click', 'button', function() {
			parent = jQuery(this).parent().attr('data-filter-group');
			sortValue = jQuery(this).attr('data-sort-value');
			jQuery(this).toggleClass('is-checked');
			var isChecked = jQuery(this).hasClass('is-checked');
			if (sortValue == 0) { // tout
				filters[parent] = ['*'];
			} else { 
				removeFilter(filters, parent,'*');
				if ( isChecked ) {
					addFilter( filters, parent,sortValue );
				} else {
					removeFilter(filters, parent, sortValue );
				}
			}
			$grid.isotope();
			updateFilterCounts();
		});
		jQuery(me+'.filter-button-group-tags').each( function( i, buttonGroup ) {
			var $buttonGroup = jQuery( buttonGroup );
			$buttonGroup.on( 'click', 'button', function() {
				if (jQuery(this).attr('data-sort-value') == 0) { // on a cliqué sur tout => on remet le reste à blanc
					jQuery(this).parent().find('.is-checked').removeClass('is-checked');
					jQuery( this ).addClass('is-checked');
				} else { // on a cliqué sur un autre bouton : uncheck le bouton tout
					jQuery(this).parent().find('[data-sort-value="0"]').removeClass('is-checked');
				}
			});
		});
	}
	if (options.displayfiltercat == "button"){
		jQuery(me+'.filter-button-group-cat').on( 'click', 'button', function() {
			parent = jQuery(this).parent().attr('data-filter-group');
			var sortValue = jQuery(this).attr('data-sort-value');
			if (sortValue == 0) {
				filters[parent] = ['*'];
			} else { 
				filters[parent]= [sortValue];
			}
			$grid.isotope(); 
			updateFilterCounts();
		});
		jQuery(me+'.filter-button-group-cat').each( function( i, buttonGroup ) {
			var $buttonGroup = jQuery( buttonGroup );
			parent = jQuery(this).parent().attr('data-filter-group');  
			$buttonGroup.on( 'click', 'button', function() {
				jQuery(this).parent().find('.is-checked').removeClass('is-checked');
				jQuery( this ).addClass('is-checked');
			});
		});
	}
	if (options.displayfilter == "button") { 
		jQuery(me+'.filter-button-group-tags').on( 'click', 'button', function() {
			parent = jQuery(this).parent().attr('data-filter-group');
			var sortValue = jQuery(this).attr('data-sort-value');
			if (sortValue == 0) {
				filters[parent] = ['*'];
			} else  { 
				filters[parent]= [sortValue];
			}
			$grid.isotope(); 
			updateFilterCounts();
		});
		jQuery(me+'.filter-button-group-tags').each( function( i, buttonGroup ) {
			var $buttonGroup = jQuery( buttonGroup );
			parent = jQuery(this).parent().attr('data-filter-group');  
			$buttonGroup.on( 'click', 'button', function() {
				jQuery(this).parent().parent().find('.is-checked').removeClass('is-checked');
				jQuery(this).addClass('is-checked');
			});
		});
	}
	jQuery(me+'.iso_button_more').on('click', function(e) {
		e.preventDefault();
		if (items_limit > 0) {
			items_limit = 0; // no limit
			jQuery(this).text(options.libless);
		} else {
			items_limit = options.limit_items; // set limit
			jQuery(this).text(options.libmore);
		}
		updateFilterCounts();
	});
	// check items limit and hide unnecessary items
	function updateFilterCounts() {
		if (jQuery(me + '.isotope_item').hasClass('iso_hide_elem')) {
			jQuery(me + '.isotope_item').removeClass('iso_hide_elem');
		}
		var itemElems = $grid.isotope('getFilteredItemElements');
		var count_items = jQuery(itemElems).length;
		if (items_limit > 0)  { 
			var index = 0;
			jQuery(itemElems).each(function () {
				if (index >= items_limit) {
					jQuery(this).addClass('iso_hide_elem');
				}
				index++;
			});
			if (index < items_limit) { // unnecessary button
				jQuery(me+'.iso_button_more').hide();
			} else { // show more button required
				jQuery(me+'.iso_button_more').show();
			}
		} 
		// 01.06.00 : hide show see less button
		if ((items_limit == 0) && (sav_limit > 0)) { 
			jQuery(itemElems).each(function () {
				if (jQuery(this).hasClass('iso_hide_elem')) {
					count_items -=1;
				}
			});
			if (count_items > sav_limit) {
				jQuery(me+'.iso_button_more').show();
			} else {
				jQuery(me+'.iso_button_more').hide();
			}
		}
		$grid.isotope();
	}
}// end of iso_cat_k2
function iso_fields(myid,options) {
	var me = "#isotope-main-"+myid+" ";
	var qsRegex;
	var parent = 'fields';
	var filters = {};
	var items_limit = options.limit_items;
	if (options.limit_items == 0) { // no limit : hide show more button
		jQuery(me+'.iso_button_more').hide();
	}
	if ((options.layout == "masonry") || (options.layout == "fitRows") || (options.layout == "packery"))
		jQuery('#isotope-main-' + myid + ' .isotope_item').css("width", (100 / parseInt(options.nbcol) -2)+"%" );
	if (options.layout == "vertical") 
		jQuery('#isotope-main-' + myid + ' .isotope_item').css("width", "100%" );
	jQuery('#isotope-main-' + myid + ' .isotope_item').css("background", options.background );
	if (parseInt(options.imgmaxheight) > 0) 
		jQuery('#isotope-main-' + myid + ' .isotope_item img').css("{max-height",options.imgmaxheight + "px");
	if (parseInt(options.imgmaxwidth) > 0) 
		jQuery('#isotope-main-' + myid + ' .isotope_item img').css("{max-height",options.imgmaxwidth + "px");
	if (options.displayfilter != "hide") {
		jQuery('.sort-by-button-group').css("float","left");
	} else {
		jQuery('.sort-by-button-group').css("float","none");
	}
	
	var $grid = jQuery(me + '.isotope_grid').imagesLoaded(function() {
		$grid.isotope({ 
			itemSelector: me + '.isotope_item',
			percentPosition: true,
			layoutMode: options.layout,
			getSortData: {
				title: '[data-title]',
				category: '[data-category]',
				date: '[data-date]',
				click: '[data-click] parseInt',
				rating: '[data-rating] parseInt',
				id: '[data-id] parseInt'
			},
			sortBy: [options.sortby],
			sortAscending: options.ascending,
			filter: function() {
				var $this = jQuery(this);
				var searchResult = qsRegex ? $this.text().match( qsRegex ) : true;
				var	lacat = $this.attr('data-category');
				var laclasse = $this.attr('class');
				var lescles = laclasse.split(" ");
				var buttonResult = false;
				var x, ix; 
				ix = 0;
				if (typeof filters === 'undefined' ) { // aucun filtre: on passe
					return searchResult && true;
				}
				// tous les groupes sur "tout"
				for (x in filters) {
					if (filters[x].indexOf('*') != -1) ix++; 
				}
				filterslength = Object.keys(filters).length;
				if (ix == filterslength) return searchResult && true;
				count = 0;
				for ( var j in lescles) {
					for (x in filters) {
						if  (filters[x].indexOf(lescles[j]) != -1) { 
							buttonResult = true;
							count += 1;
						}
					}
				}
				// 1.5.3: multi-select on grouped buttons
				if (options.searchmultiex == "true")	{
					lgth = 0;
					for (x in filters) {
						lgth = lgth + filters[x].length;
						if (filters[x].indexOf('*') != -1) lgth = lgth - 1;
					}
					return searchResult && (count == lgth);
				} else if (filterslength > 1) { 
					lgth = 0;
					for (x in filters) {
						lgth = lgth + filters[x].length;
						if (filters[x].indexOf('*') != -1) lgth = lgth - 1;
					}
					return searchResult && (count > 0) && (count <= lgth);
				} else {
				return searchResult && buttonResult;
				}
			} // end of filter
		}); // end of grid 
		updateFilterCounts();
	}); // end of imageloaded
	jQuery(me + '.isotope-div').on("refresh", function(){
 	  $grid.isotope();
	});
	jQuery(me+'.sort-by-button-group').on( 'click', 'button', function() {
		var sortValue = jQuery(this).attr('data-sort-value'),
		sens = jQuery(this).attr('data-sens');
		sortValue = sortValue.split(',');
		if (sens == "+") {
			jQuery(this).attr("data-sens","-");
			asc = true;
		} else {
			jQuery(this).attr("data-sens","+");
			asc = false;
		}
		$grid.isotope({ 
			sortBy: sortValue, 
			sortAscending: asc,
		});
	});
	jQuery(me+'.sort-by-button-group').each( function( i, buttonGroup ) {
		var $buttonGroup = jQuery( buttonGroup );
		$buttonGroup.on( 'click', 'button', function() {
			$buttonGroup.find('.is-checked').removeClass('is-checked');
			jQuery( this ).addClass('is-checked');
		});
	});
// use value of search field to filter
	var $quicksearch = jQuery(me+'.quicksearch').keyup( 
		debounce( function() {
			qsRegex = new RegExp( $quicksearch.val(), 'gi' );
			$grid.isotope();
			updateFilterCounts();
		}) 
	);
// 2.3.3 : clear serach button
	jQuery(me+'.ison-cancel-squared').on( 'click', function() {
		jQuery(me+'.quicksearch').val("");
		qsRegex = new RegExp( $quicksearch.val(), 'gi' );
		$grid.isotope();
		updateFilterCounts();
		jQuery(me+'.quicksearch').focus();
	});
	
	if  (options.displayfilter == "list") {
		jQuery(me + '.filter-button-group-fields').on( 'change', function() {
		parent = jQuery(this).attr('data-fields-group');
		var sortValue = jQuery(this).find(":selected").val();
		if (typeof filters[parent] === 'undefined' ) { 
			filters[parent] = ['*'];
		}
		if (sortValue == options.liball)   {
			filters[parent] = ['*'];
		} else { 
			filters[parent] = [sortValue];
		}
		$grid.isotope(); 
		updateFilterCounts();
		});
	}
 	if ((options.displayfilter == "multi") || (options.displayfilter == "multiex")) { 
		jQuery(me + '.filter-button-group-fields').on( 'click', 'button', function() {
			parent = jQuery(this).parent().attr('data-fields-group');
			sortValue = jQuery(this).attr('data-sort-value');
			jQuery(this).toggleClass('is-checked');
			var isChecked = jQuery(this).hasClass('is-checked');
			if (typeof filters[parent] === 'undefined' ) { 
				filters[parent] = ['*'];
			}
			if (sortValue == 0) { // tout
				filters[parent] = ['*'];
			} else { 
				removeFilter(filters, parent,'*');
				if ( isChecked ) {
					addFilter( filters, parent,sortValue );
				} else {
					removeFilter( filters, parent, sortValue );
				}
			}
			$grid.isotope();
			updateFilterCounts();
		});
		jQuery(me + '.filter-button-group-fields').each( function( i, buttonGroup ) {
			var $buttonGroup = jQuery( buttonGroup );
			$buttonGroup.on( 'click', 'button', function() {
				if (jQuery(this).attr('data-sort-value') == 0) { // on a cliqué sur tout => on remet le reste à blanc
					jQuery(this).parent().find('.is-checked').removeClass('is-checked');
					jQuery( this ).addClass('is-checked');
				} else { // on a cliqué sur un autre bouton : uncheck le bouton tout
					jQuery(this).parent().find('[data-sort-value="0"]').removeClass('is-checked');
				}
			});
		});
	}
	if (options.displayfilter == "button"){
		jQuery(me + '.filter-button-group-fields').on( 'click', 'button', function() {
			parent = jQuery(this).parent().attr('data-fields-group');
			var sortValue = jQuery(this).attr('data-sort-value');
			if (typeof filters[parent] === 'undefined' ) { 
				filters[parent] = {};
			}
			if (sortValue == 0) {
				filters[parent] = ['*'];
			} else { 
				filters[parent]= [sortValue];
			}
			$grid.isotope(); 
			updateFilterCounts();
		});
		jQuery(me + '.filter-button-group-fields').each( function( i, buttonGroup ) {
			var $buttonGroup = jQuery( buttonGroup );
			parent = jQuery(this).parent().attr('data-fields-group');  
			$buttonGroup.on( 'click', 'button', function() {
				jQuery(this).parent().find('.is-checked').removeClass('is-checked');
				jQuery( this ).addClass('is-checked');
			});
		});
	}
	jQuery(me+'.iso_button_more').on('click', function(e) {
		e.preventDefault();
		if (items_limit > 0) {
			items_limit = 0; // no limit
			jQuery(this).text(options.libless);
		} else {
			items_limit = options.limit_items; // set limit
			jQuery(this).text(options.libmore);
		}
		updateFilterCounts();
	});
	// check items limit and hide unnecessary items
	function updateFilterCounts() {
		if (jQuery(me + '.isotope_item').hasClass('iso_hide_elem')) {
			jQuery(me + '.isotope_item').removeClass('iso_hide_elem');
		}
		if (items_limit > 0) { 
			var itemElems = $grid.isotope('getFilteredItemElements');
			var count_items = jQuery(itemElems).length;
			var index = 0;
			jQuery(itemElems).each(function () {
				if (index >= items_limit) {
					jQuery(this).addClass('iso_hide_elem');
				}
				index++;
			});
			if (index < items_limit) { // unnecessary button
				jQuery(me+'.iso_button_more').hide();
			} else { // show more button required
				jQuery(me+'.iso_button_more').show();
			}
		}
		$grid.isotope();
	}

} // end of iso_fields
function debounce( fn, threshold ) {
	var timeout;
	return function debounced() {
		if ( timeout ) {
			clearTimeout( timeout );
		}
	function delayed() {
		fn();
		timeout = null;
		}
	timeout = setTimeout( delayed, threshold || 100 );
	}  
}
function addFilter( filters, parent, filter ) {
	if ( filters[parent].indexOf( filter ) == -1 ) {
		filters[parent].push( filter );
	}
}
function removeFilter( filters, parent, filter ) {
	var index = filters[parent].indexOf( filter);
	if ( index != -1 ) {
		filters[parent].splice( index, 1 );
	}
}	
