// @ts-check
import { byId, byClass, query, queryAll } from '../../dist/matt-utils.min';

document.body.innerHTML = `
	<div id="get-el-1" class="outer">
		<div id="inner-1" class="inner"></div>
	</div>
	<div id="get-el-2" class="outer">
		<div id="inner-2" class="inner"></div>
	</div>
	<div id="get-el-3" class="outer">
		<div id="inner-3" class="inner"></div>
	</div>
`;

describe( 'Selectors', () => {

	// byId
	describe( 'byId()', () => {

		test( 'Check byId', () => {

			const getEl = byId( 'get-el-1' );

			expect( getEl ).not.toBe( null );
			expect( getEl ).toBe( document.getElementById( 'get-el-1' ) );

		});

	});

	// byClass
	describe( 'byClass()', () => {

		test( 'Check byClass', () => {

			const getEls = byClass( 'outer' );

			expect( getEls.length ).not.toBe( 0 );
			expect( getEls[0] ).toBe( document.getElementsByClassName( 'outer' )[0] );
			expect( getEls[1] ).toBe( document.getElementsByClassName( 'outer' )[1] );
			expect( getEls[2] ).toBe( document.getElementsByClassName( 'outer' )[2] );

		});

		test( 'Check byClass with parent', () => {

			const parent = document.getElementById( 'get-el-1' ),
				getElsPar	 = byClass( 'inner', parent );

			expect( getElsPar.length ).not.toBe( 0 );
			expect( getElsPar[0] ).toBe( document.getElementById( 'inner-1' ) );

		});

	});

	// query
	describe( 'query()', () => {

		test( 'Check query', () => {

			const getEl = query( '#get-el-1' );

			expect( getEl ).not.toBe( null );
			expect( getEl ).toBe( document.querySelector( '#get-el-1' ) );

		});

		test( 'Check query with parent', () => {

			const parent = query( '#get-el-1' ),
				getEl			 = query( '#inner-1', parent );

			expect( getEl ).not.toBe( null );
			expect( getEl ).toBe( document.querySelector( '#inner-1' ) );

		});
	
	});

	// Query all
	describe( 'queryAll()', () => {

		test( 'Check queryAll', () => {

			const getEl = queryAll( '#get-el-1' );

			expect( getEl.length ).not.toBe( 0 );
			expect( getEl[0] ).toBe( document.querySelectorAll( '#get-el-1' )[0] );

		});

		test( 'Check queryAll with parent', () => {

			const parent = query( '#get-el-1' ),
				getEls		 = queryAll( '.inner', parent );

			expect( getEls.length ).not.toBe( 0 );
			expect( getEls[0] ).toBe( document.querySelectorAll( '.inner' )[0] );

		});
	
	});

});