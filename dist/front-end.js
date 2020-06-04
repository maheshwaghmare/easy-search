(function($){
	EasySearch = {

		_ref : null,

		init: function()
		{
			this._bind();

			// Focus.
			$( ".easy-search-input" ).focus();
		},

		/**
		 * Binds events for the Astra Sites.
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
			$( document ).on('keyup', '.easy-search-input', EasySearch._search );
			$( document ).on('click', '.easy-search-close', EasySearch._close_search );
		},

		_close_search: function() {
			$(this).removeClass('loading');
			$('.easy-search-input').val( '' );
			$('.easy-search-result').html( '' );
		},

		/**
		 * Search Site.
		 *
		 * Prepare Before API Request:
		 * - Remove Inline Height
		 * - Added 'hide-me' class to hide the 'No more sites!' string.
		 * - Added 'loading-content' for body.
		 * - Show spinner.
		 */
		_search: function() {
			var search_term = $('.easy-search-input').val() || '';
			if( ! search_term ) {
				return;
			}

			window.clearTimeout(EasySearch._ref);
			EasySearch._ref = window.setTimeout(function () {
				EasySearch._ref = null;

				EasySearch._process_request();

			}, 1000);
		},

		_process_request: function() {

			// Loading.
			$('.easy-search-spinner').addClass('loading');
			$('.easy-search-close').removeClass('loading');

			var search_term = $('.easy-search-input').val() || '';
			var subtype = $('.easy-search-input').attr('data-subtype') || '';

			var params = {
				reference: EasySearchVars.reference,
			};

			if( search_term ) {
				params['search'] = search_term;
			}

			if( subtype ) {
				params['subtype'] = subtype;
			}

			// API Request.
			var api_post = {
				slug: '?' + decodeURIComponent( $.param( params ) )
			};

			EasySearch._api_request( api_post, function( data ) {
				console.log( 'data: ' );
				console.log( data );

				var template = wp.template('easy-search-items');
				if( parseInt( data.items_count ) ) {
					$('.easy-search-result').html( template( data ) );
				} else {
					$('.easy-search-result').html( wp.template('easy-search-no-docs-found') );
				}

				// Remove loader.
				$('.easy-search-spinner').removeClass('loading');
				$('.easy-search-close').addClass('loading');
			} );
		},

		_api_request: function( args, callback ) {

			// Set API Request Data.
			var data = {
				url     : EasySearchVars.api_url + args.slug,
				cache   : false,
				args    : args,
				callback : callback,
			};

			// // Set headers.
			// data.headers = CartFlowsImportVars.headers;

			$.ajax( data )
			.done(function( items, status, XHR ) {

				if( 'success' === status && XHR.getResponseHeader('x-wp-total') ) {

					var data = {
						args        : args,
						items       : items,
						items_count : XHR.getResponseHeader('x-wp-total') || 0,
						callback     : callback,
						
						// AJAX response.
						status : status,
						XHR    : XHR,
					};

					if( undefined !== args.trigger && '' !== args.trigger ) {
						$(document).trigger( args.trigger, [data] );
					}

				} else {
					$(document).trigger( 'easy-search-api-request-error' );
				}

				if( callback && typeof callback == "function"){
					callback( data );
			    }
			})
			.fail(function( jqXHR, textStatus ) {

				$(document).trigger( 'easy-search-api-request-fail' );

			})
			.always(function() {

				$(document).trigger( 'easy-search-api-request-always' );

			});

		}

	};

	/**
	 * Initialize EasySearch
	 */
	$(function(){
		EasySearch.init();
	});

})(jQuery);
