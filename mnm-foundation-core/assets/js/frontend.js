/* MNM Foundation Core: form validation + AJAX submit, gallery filter, lightbox, counters, progress. Vanilla JS. */
( function () {
	'use strict';
	var cfg = window.mnmCore || { i18n: {} };
	var t = cfg.i18n;
	document.documentElement.classList.add( 'js' );

	/* ---------- Forms ---------- */
	function fieldWrap( el ) { return el.closest( '.mnm-field' ); }
	function setError( el, msg ) {
		var w = fieldWrap( el ); if ( ! w ) { return; }
		var out = w.querySelector( '.mnm-error' );
		w.classList.toggle( 'has-error', !! msg );
		if ( msg ) { el.setAttribute( 'aria-invalid', 'true' ); } else { el.removeAttribute( 'aria-invalid' ); }
		if ( out ) { out.textContent = msg || ''; }
	}
	function validateField( el ) {
		var v = ( el.type === 'checkbox' ) ? el.checked : el.value.trim(), msg = '';
		if ( el.required && ( ! v ) ) { msg = t.required; }
		else if ( el.type === 'email' && v && ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( v ) ) { msg = t.email; }
		else if ( el.type === 'tel' && v && ! /^[0-9+()\-\s]{7,20}$/.test( v ) ) { msg = t.phone; }
		setError( el, msg );
		return ! msg;
	}
	document.querySelectorAll( '[data-mnm-form]' ).forEach( function ( form ) {
		var fields = form.querySelectorAll( 'input:not([type=hidden]):not(.mnm-hp input), select, textarea' );
		var status = form.querySelector( '[data-mnm-status]' );
		var wrap = form.closest( '[data-mnm-reg]' );
		fields.forEach( function ( el ) {
			el.addEventListener( 'blur', function () { if ( el.name !== 'mnm_website' ) { validateField( el ); } } );
			el.addEventListener( 'input', function () { if ( fieldWrap( el ) && fieldWrap( el ).classList.contains( 'has-error' ) ) { validateField( el ); } } );
		} );
		form.addEventListener( 'submit', function ( e ) {
			var first = null, ok = true;
			fields.forEach( function ( el ) {
				if ( el.name === 'mnm_website' ) { return; }
				if ( ! validateField( el ) ) { ok = false; if ( ! first ) { first = el; } }
			} );
			if ( ! ok ) { e.preventDefault(); if ( first ) { first.focus(); } return; }
			if ( ! window.fetch || ! window.FormData ) { return; } // Fall back to normal post.
			e.preventDefault();
			var btn = form.querySelector( '[type=submit]' ), label = btn.textContent;
			btn.disabled = true; btn.textContent = t.sending;
			var data = new FormData( form ); data.append( 'mnm_ajax', '1' );
			fetch( cfg.postUrl, { method: 'POST', body: data, credentials: 'same-origin' } )
				.then( function ( r ) { return r.json().then( function ( j ) { return j; } ); } )
				.then( function ( j ) {
					btn.disabled = false; btn.textContent = label;
					if ( j.ok ) {
						form.hidden = true;
						var s = wrap.querySelector( '[data-mnm-success]' ), ref = wrap.querySelector( '[data-mnm-ref]' );
						if ( ref && j.ref ) { ref.textContent = j.ref; }
						s.hidden = false; s.focus(); s.scrollIntoView( { behavior: 'smooth', block: 'center' } );
						document.querySelectorAll( '[data-mnm-regcount]' ).forEach( function ( n ) { if ( j.count ) { n.textContent = j.count; } } );
					} else {
						if ( status ) { status.textContent = j.message || t.error; }
						if ( j.errors ) {
							Object.keys( j.errors ).forEach( function ( k ) {
								var el = form.querySelector( '[name$="[' + k + ']"], [name="' + k + '"]' );
								if ( el ) { setError( el, j.errors[ k ] ); }
							} );
						}
						var bad = form.querySelector( '.has-error input, .has-error select, .has-error textarea' ); if ( bad ) { bad.focus(); }
					}
				} )
				.catch( function () { btn.disabled = false; btn.textContent = label; if ( status ) { status.textContent = t.error; } } );
		} );
	} );

	/* ---------- Gallery filter ---------- */
	document.querySelectorAll( '[data-mnm-gallery]' ).forEach( function ( g ) {
		var btns = g.querySelectorAll( '.mnm-gallery__filters button' ), items = g.querySelectorAll( '.mnm-gallery__item' );
		btns.forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				var f = b.getAttribute( 'data-filter' );
				btns.forEach( function ( x ) { x.classList.toggle( 'is-active', x === b ); x.setAttribute( 'aria-pressed', x === b ? 'true' : 'false' ); } );
				items.forEach( function ( it ) { it.hidden = ! ( f === '*' || ( ' ' + it.getAttribute( 'data-cats' ) + ' ' ).indexOf( ' ' + f + ' ' ) > -1 ); } );
			} );
		} );
	} );

	/* ---------- Lightbox ---------- */
	var lb, lbImg, lbCap, list = [], idx = 0, opener = null;
	function build() {
		lb = document.createElement( 'div' ); lb.className = 'mnm-lb'; lb.setAttribute( 'role', 'dialog' ); lb.setAttribute( 'aria-modal', 'true' ); lb.setAttribute( 'aria-label', 'Image viewer' );
		lb.innerHTML = '<button type="button" class="mnm-lb__close"></button><button type="button" class="mnm-lb__prev"></button><button type="button" class="mnm-lb__next"></button><img alt="" /><p class="mnm-lb__cap"></p>';
		lb.querySelector( '.mnm-lb__close' ).innerHTML = '&times;'; lb.querySelector( '.mnm-lb__close' ).setAttribute( 'aria-label', t.close || 'Close' );
		lb.querySelector( '.mnm-lb__prev' ).innerHTML = '&#8249;'; lb.querySelector( '.mnm-lb__prev' ).setAttribute( 'aria-label', t.prev || 'Previous' );
		lb.querySelector( '.mnm-lb__next' ).innerHTML = '&#8250;'; lb.querySelector( '.mnm-lb__next' ).setAttribute( 'aria-label', t.next || 'Next' );
		lbImg = lb.querySelector( 'img' ); lbCap = lb.querySelector( '.mnm-lb__cap' );
		lb.addEventListener( 'click', function ( e ) { if ( e.target === lb ) { close(); } } );
		lb.querySelector( '.mnm-lb__close' ).addEventListener( 'click', close );
		lb.querySelector( '.mnm-lb__prev' ).addEventListener( 'click', function () { go( -1 ); } );
		lb.querySelector( '.mnm-lb__next' ).addEventListener( 'click', function () { go( 1 ); } );
		document.body.appendChild( lb );
	}
	function show() {
		var a = list[ idx ], im = a.querySelector( 'img' );
		lbImg.src = a.href; lbImg.alt = im ? im.alt : ''; lbCap.textContent = a.getAttribute( 'data-caption' ) || '';
	}
	function go( d ) { idx = ( idx + d + list.length ) % list.length; show(); }
	function close() { lb.classList.remove( 'is-open' ); document.body.style.overflow = ''; if ( opener ) { opener.focus(); } }
	document.addEventListener( 'click', function ( e ) {
		var a = e.target.closest( 'a[data-lightbox]' ); if ( ! a ) { return; }
		e.preventDefault(); if ( ! lb ) { build(); }
		var scope = a.closest( '.mnm-gallery__grid' ) || document;
		list = Array.prototype.filter.call( scope.querySelectorAll( 'a[data-lightbox]' ), function ( x ) { return ! x.closest( '[hidden]' ); } );
		idx = list.indexOf( a ); opener = a; show(); lb.classList.add( 'is-open' ); document.body.style.overflow = 'hidden';
		lb.querySelector( '.mnm-lb__close' ).focus();
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( ! lb || ! lb.classList.contains( 'is-open' ) ) { return; }
		if ( e.key === 'Escape' ) { close(); }
		else if ( e.key === 'ArrowLeft' ) { go( -1 ); }
		else if ( e.key === 'ArrowRight' ) { go( 1 ); }
		else if ( e.key === 'Tab' ) { // Simple focus trap.
			var f = lb.querySelectorAll( 'button' ), first = f[ 0 ], last = f[ f.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) { e.preventDefault(); last.focus(); }
			else if ( ! e.shiftKey && document.activeElement === last ) { e.preventDefault(); first.focus(); }
		}
	} );

	/* ---------- Counters + progress on scroll ---------- */
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	function count( el ) {
		var end = parseFloat( el.getAttribute( 'data-count' ) ), pre = el.getAttribute( 'data-prefix' ) || '', suf = el.getAttribute( 'data-suffix' ) || '';
		if ( isNaN( end ) || reduce ) { return; }
		var start = null, dur = 1400;
		function fmt( n ) { return pre + Math.round( n ).toLocaleString( 'en-IN' ) + suf; }
		function step( ts ) { if ( start === null ) { start = ts; } var p = Math.min( 1, ( ts - start ) / dur ); el.textContent = fmt( end * ( 1 - Math.pow( 1 - p, 3 ) ) ); if ( p < 1 ) { requestAnimationFrame( step ); } }
		el.textContent = fmt( 0 ); requestAnimationFrame( step );
	}
	var targets = document.querySelectorAll( '.mnm-progress, .mnm-stat__value[data-count]' );
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( es ) {
			es.forEach( function ( en ) {
				if ( ! en.isIntersecting ) { return; }
				en.target.classList.add( 'is-in' );
				if ( en.target.hasAttribute( 'data-count' ) ) { count( en.target ); }
				io.unobserve( en.target );
			} );
		}, { threshold: .35 } );
		targets.forEach( function ( n ) { io.observe( n ); } );
	} else { targets.forEach( function ( n ) { n.classList.add( 'is-in' ); } ); }
} )();
