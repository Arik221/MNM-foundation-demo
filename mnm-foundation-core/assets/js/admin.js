/* MNM Foundation Core: media picker for image / gallery fields. */
( function ( $ ) {
	'use strict';
	function sync( $f ) {
		var ids = [];
		$f.find( '.mnm-media-preview li' ).each( function () { ids.push( $( this ).data( 'id' ) ); } );
		$f.find( '.mnm-media-ids' ).val( ids.join( ',' ) );
	}
	$( document ).on( 'click', '.mnm-media-pick', function ( e ) {
		e.preventDefault();
		var $f = $( this ).closest( '.mnm-media-field' ), multiple = String( $f.data( 'multiple' ) ) === '1';
		var frame = wp.media( { title: multiple ? 'Choose images' : 'Choose an image', button: { text: 'Use selected' }, library: { type: 'image' }, multiple: multiple ? 'add' : false } );
		frame.on( 'open', function () {
			var sel = frame.state().get( 'selection' );
			$f.find( '.mnm-media-preview li' ).each( function () { var a = wp.media.attachment( $( this ).data( 'id' ) ); a.fetch(); sel.add( a ); } );
		} );
		frame.on( 'select', function () {
			var $ul = $f.find( '.mnm-media-preview' ).empty();
			frame.state().get( 'selection' ).each( function ( a ) {
				var j = a.toJSON(), url = ( j.sizes && j.sizes.thumbnail ) ? j.sizes.thumbnail.url : j.url;
				$ul.append( $( '<li/>' ).attr( 'data-id', j.id ).append( $( '<img/>' ).attr( 'src', url ).attr( 'alt', '' ) ).append( $( '<button type="button" class="mnm-media-remove" aria-label="Remove image">&times;</button>' ) ) );
			} );
			sync( $f );
		} );
		frame.open();
	} );
	$( document ).on( 'click', '.mnm-media-remove', function () { var $f = $( this ).closest( '.mnm-media-field' ); $( this ).closest( 'li' ).remove(); sync( $f ); } );
	$( function () { $( '.mnm-media-preview' ).sortable( { update: function () { sync( $( this ).closest( '.mnm-media-field' ) ); } } ); } );
} )( jQuery );
