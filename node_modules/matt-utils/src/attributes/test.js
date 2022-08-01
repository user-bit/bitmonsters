// @ts-check
import { byId, query, getAttr, setAttr, remAttr, hasAttr } from '../../dist/matt-utils.min';

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

describe( 'Attributes', () => {

	// byId
	describe( 'byId()', () => {

		test( 'Check setAttr', () => {

			const getEl = byId( 'get-el-1' );

			expect( getEl ).not.toBe( null );

			setAttr( getEl, 'new-attr', '2' );
			expect( getEl.outerHTML ).toBe( `<div id="get-el-1" class="outer" new-attr="2">
		<div id="inner-1" class="inner"></div>
	</div>` );

			remAttr( getEl, 'new-attr' );

		});

		test( 'Check getAttr', () => {

			const getEl = byId( 'get-el-1' );

			expect( getEl ).not.toBe( null );

			setAttr( getEl, 'new-attr', '2' );

			const newAttr = getAttr( getEl, 'new-attr' );
			expect( newAttr ).toBe( '2' );

			remAttr( getEl, 'new-attr' );

		});

		test( 'Check hasAttr', () => {

			const getEl = byId( 'get-el-1' );

			expect( getEl ).not.toBe( null );

			setAttr( getEl, 'new-attr', '2' );

			expect( hasAttr( getEl, 'new-attr' ) ).toBe( true );
			expect( hasAttr( getEl, 'otherAttr' ) ).toBe( false );

			remAttr( getEl, 'new-attr' );

		});

		test( 'Check remAttr', () => {

			const getEl = byId( 'get-el-1' );

			expect( getEl ).not.toBe( null );

			setAttr( getEl, 'new-attr', '2' );
			remAttr( getEl, 'new-attr' );

			expect( getEl.getAttribute( 'new-attr' ) ).toBe( null );

		});

	});

	// query
	describe( 'query()', () => {

		test( 'Check setAttr', () => {

			const getEl = query( '#get-el-1' );

			expect( getEl ).not.toBe( null );

			setAttr( getEl, 'new-attr', '2' );
			expect( getEl.outerHTML ).toBe( `<div id="get-el-1" class="outer" new-attr="2">
		<div id="inner-1" class="inner"></div>
	</div>` );

			remAttr( getEl, 'new-attr' );

		});

		test( 'Check getAttr', () => {

			const getEl = query( '#get-el-1' );

			expect( getEl ).not.toBe( null );

			setAttr( getEl, 'new-attr', '2' );

			const newAttr = getAttr( getEl, 'new-attr' );
			expect( newAttr ).toBe( '2' );

			remAttr( getEl, 'new-attr' );

		});

		test( 'Check hasAttr', () => {

			const getEl = query( '#get-el-1' );

			setAttr( getEl, 'new-attr', '2' );

			expect( getEl ).not.toBe( null );
			expect( hasAttr( getEl, 'new-attr' ) ).toBe( true );
			expect( hasAttr( getEl, 'otherAttr' ) ).toBe( false );

			remAttr( getEl, 'new-attr' );

		});

		test( 'Check remAttr', () => {

			const getEl = query( '#get-el-1' );

			expect( getEl ).not.toBe( null );

			setAttr( getEl, 'new-attr', '2' );
			remAttr( getEl, 'new-attr' );

			expect( getEl.getAttribute( 'new-attr' ) ).toBe( null );

		});

	});

});