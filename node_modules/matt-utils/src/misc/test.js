// @ts-check
import { byId, getElementIndex } from '../../dist/matt-utils.min';

document.body.innerHTML = `
	<div id="get-el-1" class="outer">
		<div id="inner-1" class="inner"></div>
	</div>
	<div id="get-el-2" class="outer">
		<div id="inner-2" class="inner"></div>
	</div>
	<div id="get-el-3" class="outer">
		<div id="inner-3" class="inner"></div>
		<div id="inner-3-1" class="inner"></div>
		<div id="inner-3-2" class="inner"></div>
		<div id="inner-3-3" class="inner"></div>
	</div>
`;

describe( 'Indexes', () => {

	// add 1 class
	test( 'Get parent index', () => {

		const el = byId( 'get-el-3' );

		expect( el ).not.toBe( null );
		expect( getElementIndex( el ) ).toEqual( 2 );
	});

	test( 'Get child index', () => {

		const el = byId( 'inner-3-3' );

		expect( el ).not.toBe( null );
		expect( getElementIndex( el ) ).toEqual( 3 );
	});

});