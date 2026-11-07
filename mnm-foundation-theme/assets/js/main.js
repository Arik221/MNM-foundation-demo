/* MNM Foundation Theme: navigation and motion layer. Vanilla JS, no dependencies.
   Motion uses transform/opacity only, is gated for fine pointers, and is skipped under prefers-reduced-motion. */
( function () {
	'use strict';
	var root = document.documentElement;
	root.classList.add( 'js' );
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var finePointer = window.matchMedia && window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;
	var $ = function ( s, c ) { return Array.prototype.slice.call( ( c || document ).querySelectorAll( s ) ); };

	/* ---------- Mobile navigation ---------- */
	var toggle = document.querySelector( '[data-nav-toggle]' ), nav = document.querySelector( '[data-nav]' );
	function setNav( open ) {
		if ( ! toggle || ! nav ) { return; }
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		nav.classList.toggle( 'is-open', open );
		document.body.classList.toggle( 'nav-open', open );
	}
	if ( toggle && nav ) {
		$( '.menu > li', nav ).forEach( function ( li, i ) { li.style.setProperty( '--k', i ); } );
		toggle.addEventListener( 'click', function () { setNav( toggle.getAttribute( 'aria-expanded' ) !== 'true' ); } );
		document.addEventListener( 'keydown', function ( e ) { if ( e.key === 'Escape' && nav.classList.contains( 'is-open' ) ) { setNav( false ); toggle.focus(); } } );
		nav.addEventListener( 'click', function ( e ) { if ( e.target.closest( 'a' ) ) { setNav( false ); } } );
		window.matchMedia( '(min-width: 961px)' ).addEventListener( 'change', function () { setNav( false ); } );
	}

	/* ---------- Tab-bar indicator that slides to the hovered / current item ---------- */
	var menu = nav && nav.querySelector( '.menu' );
	if ( menu && ! reduce ) {
		var ind = document.createElement( 'span' ); ind.className = 'nav-ind'; ind.setAttribute( 'aria-hidden', 'true' ); menu.insertBefore( ind, menu.firstChild );
		var cur = menu.querySelector( '.current-menu-item, [aria-current]' ); cur = cur ? cur.closest( 'li' ) : null;
		var moveTo = function ( li ) {
			if ( ! li ) { ind.style.opacity = 0; return; }
			ind.style.width = li.offsetWidth + 'px'; ind.style.transform = 'translateX(' + li.offsetLeft + 'px)'; ind.style.opacity = 1;
		};
		$( ':scope > li', menu ).forEach( function ( li ) {
			li.addEventListener( 'mouseenter', function () { moveTo( li ); } );
			li.addEventListener( 'focusin', function () { moveTo( li ); } );
		} );
		menu.addEventListener( 'mouseleave', function () { moveTo( cur ); } );
		menu.addEventListener( 'focusout', function () { moveTo( cur ); } );
		window.addEventListener( 'resize', function () { moveTo( cur ); } );
		( document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve() ).then( function () { moveTo( cur ); } );
		moveTo( cur );
	}

	/* ---------- Tabs (ARIA tablist with arrow-key support) ---------- */
	$( '[data-tabs]' ).forEach( function ( box ) {
		var tabs = $( '[role=tab]', box ), panels = $( '[role=tabpanel]', box );
		function select( i, focus ) {
			tabs.forEach( function ( t, k ) { var on = k === i; t.setAttribute( 'aria-selected', on ? 'true' : 'false' ); t.tabIndex = on ? 0 : -1; panels[ k ].hidden = ! on; } );
			box.setAttribute( 'data-accent', panels[ i ].getAttribute( 'data-accent' ) || 'blue' );
			if ( focus ) { tabs[ i ].focus(); }
		}
		tabs.forEach( function ( t, i ) {
			t.addEventListener( 'click', function () { select( i ); } );
			t.addEventListener( 'keydown', function ( e ) {
				var k = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
				if ( k ) { e.preventDefault(); select( ( i + k + tabs.length ) % tabs.length, true ); }
				else if ( e.key === 'Home' ) { e.preventDefault(); select( 0, true ); }
				else if ( e.key === 'End' ) { e.preventDefault(); select( tabs.length - 1, true ); }
			} );
		} );
	} );

	/* Background video: respect reduced motion */
	if ( reduce ) { $( 'video[autoplay]' ).forEach( function ( v ) { v.removeAttribute( 'autoplay' ); v.pause(); } ); }

	/* ---------- Split headings into words ---------- */
	function wrapWords( el, cls ) {
		var text = el.textContent.replace( /\s+/g, ' ' ).trim(), frag = document.createDocumentFragment();
		text.split( ' ' ).forEach( function ( word, i, all ) {
			var outer = document.createElement( 'span' ), inner = document.createElement( 'span' );
			outer.className = cls; inner.textContent = word; outer.style.setProperty( '--i', i );
			if ( cls === 'w' ) { outer.appendChild( inner ); } else { outer.textContent = word; }
			frag.appendChild( outer ); if ( i < all.length - 1 ) { frag.appendChild( document.createTextNode( ' ' ) ); }
		} );
		if ( ! el.getAttribute( 'aria-label' ) && ! el.closest( '[aria-label]' ) ) { el.setAttribute( 'aria-label', text ); }
		el.textContent = ''; el.appendChild( frag );
		$( '.' + cls, el ).forEach( function ( s ) { s.setAttribute( 'aria-hidden', 'true' ); } );
	}
	$( '[data-split]' ).forEach( function ( el ) { wrapWords( el, 'w' ); el.classList.add( 'is-split' ); } );
	var words = $( '[data-words]' ).map( function ( el ) { wrapWords( el, 'word' ); return { el: el, w: $( '.word', el ) }; } );

	/* ---------- Reveal on scroll (with sibling stagger) ---------- */
	$( '.grid, .bento, .event-list, .news-grid__side, .values, .faces, .races, .mnm-stats, .memorial__grid, .mvd__grid' ).forEach( function ( box ) {
		$( ':scope > .reveal, :scope > li.reveal, :scope > .tile, :scope > .card, :scope > .mnm-stat', box ).forEach( function ( c, i ) { c.style.setProperty( '--i', Math.min( i, 5 ) ); } );
	} );
	var targets = $( '.reveal, .is-split' );
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( en ) { if ( en.isIntersecting ) { en.target.classList.add( 'is-in' ); io.unobserve( en.target ); } } );
		}, { threshold: .15, rootMargin: '0px 0px -8% 0px' } );
		targets.forEach( function ( el ) { io.observe( el ); } );
	} else { targets.forEach( function ( el ) { el.classList.add( 'is-in' ); } ); }

	/* ---------- Scroll-driven: header, progress, parallax depth, word highlight ---------- */
	var header = document.querySelector( '[data-header]' ), bar = document.querySelector( '[data-progress]' );
	var heroes = $( '[data-hero], [data-hero-lite]' ).map( function ( h ) { return { el: h, layers: $( '[data-depth]', h ) }; } );
	var parallax = $( '[data-parallax]' ), px = 0, py = 0, ticking = false;
	var stage = document.querySelector( '[data-stage]' ), glow = document.querySelector( '[data-follow]' ), gx = 0, gy = 0, tx = 0, ty = 0, glowRaf = 0;
	function frame() {
		ticking = false;
		var y = window.scrollY || window.pageYOffset, vh = window.innerHeight, max = document.documentElement.scrollHeight - vh;
		if ( header ) { header.classList.toggle( 'is-scrolled', y > 24 ); }
		if ( bar && max > 0 ) { bar.style.transform = 'scaleX(' + Math.min( 1, y / max ).toFixed( 4 ) + ')'; }
		if ( reduce ) { return; }
		heroes.forEach( function ( h ) {
			if ( y > h.el.offsetHeight * 1.1 ) { return; }
			h.layers.forEach( function ( l ) {
				var d = parseFloat( l.getAttribute( 'data-depth' ) );
				l.style.transform = 'translate3d(' + ( px * d * 60 ).toFixed( 1 ) + 'px,' + ( -y * d * 0.6 ).toFixed( 1 ) + 'px,0)';
			} );
		} );
		parallax.forEach( function ( el ) {
			var r = el.getBoundingClientRect(); if ( r.bottom < -100 || r.top > vh + 100 ) { return; }
			var s = parseFloat( el.getAttribute( 'data-parallax' ) ) || .05, img = el.firstElementChild || el;
			img.style.transform = 'translate3d(0,' + ( ( r.top + r.height / 2 - vh / 2 ) * -s ).toFixed( 1 ) + 'px,0)';
		} );
		words.forEach( function ( g ) {
			var r = g.el.getBoundingClientRect(); if ( r.top > vh || r.bottom < 0 ) { return; }
			var p = Math.max( 0, Math.min( 1, ( vh * .82 - r.top ) / ( r.height + vh * .32 ) ) ), n = Math.round( p * g.w.length );
			g.w.forEach( function ( w, i ) { w.classList.toggle( 'on', i < n ); } );
		} );
	}
	function request() { if ( ! ticking ) { ticking = true; requestAnimationFrame( frame ); } }
	window.addEventListener( 'scroll', request, { passive: true } );
	window.addEventListener( 'resize', request );
	if ( finePointer && ! reduce ) {
		window.addEventListener( 'pointermove', function ( e ) {
			px = ( e.clientX / window.innerWidth - .5 ) * 2; py = ( e.clientY / window.innerHeight - .5 ) * 2;
			if ( stage && window.scrollY < window.innerHeight ) { stage.style.transform = 'perspective(1300px) rotateY(' + ( px * 9 ).toFixed( 2 ) + 'deg) rotateX(' + ( -py * 6 ).toFixed( 2 ) + 'deg)'; }
			if ( glow ) { var r = glow.parentNode.getBoundingClientRect(); tx = e.clientX - r.left; ty = e.clientY - r.top; if ( ! glowRaf ) { glowRaf = requestAnimationFrame( moveGlow ); } }
			request();
		}, { passive: true } );
		function moveGlow() {
			gx += ( tx - gx ) * .12; gy += ( ty - gy ) * .12;
			glow.style.transform = 'translate3d(' + gx.toFixed( 1 ) + 'px,' + gy.toFixed( 1 ) + 'px,0)';
			glowRaf = ( Math.abs( tx - gx ) + Math.abs( ty - gy ) > .5 ) ? requestAnimationFrame( moveGlow ) : 0;
		}
	}
	frame();

	/* ---------- 3D tilt + magnetic buttons (fine pointers only) ---------- */
	if ( finePointer && ! reduce ) {
		$( '[data-tilt]' ).forEach( function ( el ) {
			el.addEventListener( 'pointermove', function ( e ) {
				if ( e.pointerType === 'touch' ) { return; }
				var r = el.getBoundingClientRect(), x = ( e.clientX - r.left ) / r.width, y = ( e.clientY - r.top ) / r.height;
				el.classList.add( 'is-tilting' );
				el.style.transform = 'perspective(1000px) rotateX(' + ( ( .5 - y ) * 7 ).toFixed( 2 ) + 'deg) rotateY(' + ( ( x - .5 ) * 9 ).toFixed( 2 ) + 'deg) translateZ(0)';
			} );
			el.addEventListener( 'pointerleave', function () { el.classList.remove( 'is-tilting' ); el.style.transform = ''; } );
		} );
		$( '[data-magnetic]' ).forEach( function ( el ) {
			el.addEventListener( 'pointermove', function ( e ) {
				var r = el.getBoundingClientRect(), dx = e.clientX - ( r.left + r.width / 2 ), dy = e.clientY - ( r.top + r.height / 2 );
				el.style.translate = ( dx * .22 ).toFixed( 1 ) + 'px ' + ( dy * .3 ).toFixed( 1 ) + 'px';
			} );
			el.addEventListener( 'pointerleave', function () { el.style.translate = ''; } );
		} );
	}
} )();
