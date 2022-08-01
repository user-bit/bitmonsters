// @ts-check
import { byId, byClass, query, queryAll, addClass, removeClass, hasClass } from '../../dist/matt-utils.min';

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

describe( 'Classes', () => {

	// byId
	describe( 'byId()', () => {

		// add 1 class
		test( 'Add single class', () => {

			let el = byId( 'get-el-1' );

			expect( el ).not.toBe( null );
			addClass( el, 'test-byId' );

			expect( el.getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byId' ) );

			removeClass( el, 'test-byId' );

		});

		test( 'Has class', () => {

			let el = byId( 'get-el-1' );

			expect( el ).not.toBe( null );

			addClass( el, 'test-byId' );

			expect( hasClass( el, 'test-byId' ) ).toEqual( true );
			expect( hasClass( el, 'test-random' ) ).toEqual( false );

			removeClass( el, 'test-byId' );

		});

		// remove 1 class
		test( 'Remove single class', () => {

			let el = byId( 'get-el-1' );

			expect( el ).not.toBe( null );

			addClass( el, 'test-byId' );
			removeClass( el, 'test-byId' );

			expect( el.getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byId' ) );

		});

		// add 2 classes
		test( 'Add multiple classes', () => {

			let el = byId( 'get-el-1' );

			expect( el ).not.toBe( null );
			addClass( el, 'test-byId', 'test-byId-2' );

			expect( el.getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byId-2' ) );
			expect( el.getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byId' ) );

			removeClass( el, 'test-byId', 'test-byId-2' );

		});

		test( 'Has class multiple', () => {

			let el = byId( 'get-el-1' );

			expect( el ).not.toBe( null );

			addClass( el, 'test-byId', 'test-byId-2' );

			expect( hasClass( el, 'test-byId', 'test-byId-2' ) ).toEqual( true );
			expect( hasClass( el, 'test-byId', 'test-random' ) ).toEqual( false );
			expect( hasClass( el, 'test-random', 'test-byId-2' ) ).toEqual( false );

			removeClass( el, 'test-byId', 'test-byId-2' );

		});

		// remove 2 classes
		test( 'Remove multiple classes', () => {

			let el = byId( 'get-el-1' );

			expect( el ).not.toBe( null );

			addClass( el, 'test-byId', 'test-byId-2' );
			removeClass( el, 'test-byId', 'test-byId-2' );

			expect( el.getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byId-2' ) );
			expect( el.getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byId' ) );

		});

	});

	// query
	describe( 'query()', () => {

		// add 1 class
		test( 'Add single class', () => {

			let el = query( '#get-el-1' );

			expect( el ).not.toBe( null );
			addClass( el, 'test-query' );

			expect( el.getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-query' ) );

			removeClass( el, 'test-query' );

		});

		test( 'Has class', () => {

			let el = query( '#get-el-1' );

			expect( el ).not.toBe( null );

			addClass( el, 'test-query' );

			expect( hasClass( el, 'test-query' ) ).toEqual( true );
			expect( hasClass( el, 'test-random' ) ).toEqual( false );

			removeClass( el, 'test-query' );

		});

		// remove 1 class
		test( 'Remove single class', () => {

			let el = query( '#get-el-1' );

			expect( el ).not.toBe( null );
			addClass( el, 'test-query' );
			removeClass( el, 'test-query' );

			expect( el.getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-query' ) );

		});

		// add 2 classes
		test( 'Add multiple classes', () => {

			let el = query( '#get-el-1' );

			expect( el ).not.toBe( null );
			addClass( el, 'test-query', 'test-query-2' );

			expect( el.getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-query-2' ) );
			expect( el.getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-query' ) );

			removeClass( el, 'test-query', 'test-query-2' );

		});

		test( 'Has class multiple', () => {

			let el = query( '#get-el-1' );

			expect( el ).not.toBe( null );

			addClass( el, 'test-query', 'test-query-2' );

			expect( hasClass( el, 'test-query', 'test-query-2' ) ).toEqual( true );
			expect( hasClass( el, 'test-query', 'test-random' ) ).toEqual( false );
			expect( hasClass( el, 'test-random', 'test-query-2' ) ).toEqual( false );

			removeClass( el, 'test-query', 'test-query-2' );

		});

		// remove 2 classes
		test( 'Remove multiple classes', () => {

			let el = query( '#get-el-1' );

			expect( el ).not.toBe( null );

			addClass( el, 'test-query', 'test-query-2' );
			removeClass( el, 'test-query', 'test-query-2' );

			expect( el.getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-query-2' ) );
			expect( el.getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-query' ) );
		});

	});

	// byClass
	describe( 'byClass()', () => {

		// add 1 class
		test( 'Add single class to single el', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el[0], 'test-byClass' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass' ) );

			removeClass( el[0], 'test-byClass' );

		});

		test( 'Has class', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el[0], 'test-byClass' );

			expect( hasClass( el[0], 'test-byClass' ) ).toEqual( true );
			expect( hasClass( el[0], 'test-random' ) ).toEqual( false );

			removeClass( el[0], 'test-byClass' );

		});

		// remove 1 class
		test( 'Remove single class from single el', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el[0], 'test-byClass' );
			removeClass( el[0], 'test-byClass' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass' ) );
		});

		// add 1 class to 3 els
		test( 'Add single class to multiple el', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el, 'test-byClass' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass' ) );

			removeClass( el, 'test-byClass' );

		});

		test( 'Has single class multiple elements', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el[0], 'test-byClass' );

			expect( hasClass( el, 'test-byClass' ) ).toEqual( false );
			expect( hasClass( el, 'test-random' ) ).toEqual( false );

			addClass( el, 'test-byClass-mult' );
			expect( hasClass( el, 'test-byClass-mult' ) ).toEqual( true );

			removeClass( el, 'test-byClass', 'test-byClass-mult' );

		});

		// remove 1 class to 3 els
		test( 'Remove single class from multiple el', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el, 'test-byClass' );
			removeClass( el, 'test-byClass' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass' ) );
		});

		// add 2 classes
		test( 'Add multiple classes to single el', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el[0], 'test-byClass', 'test-byClass-2' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass-2' ) );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass' ) );

			removeClass( el[0], 'test-byClass', 'test-byClass-2' );

		});

		test( 'Has class multiple', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el[0], 'test-byClass', 'test-byClass-2' );

			expect( hasClass( el[0], 'test-byClass', 'test-byClass-2' ) ).toEqual( true );
			expect( hasClass( el[0], 'test-byClass', 'test-random' ) ).toEqual( false );
			expect( hasClass( el[0], 'test-random', 'test-byClass-2' ) ).toEqual( false );

			removeClass( el[0], 'test-byClass', 'test-byClass-2' );

		});

		// remove 2 classes
		test( 'Remove multiple classes from single el', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el[0], 'test-byClass', 'test-byClass-2' );
			removeClass( el[0], 'test-byClass', 'test-byClass-2' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass-2' ) );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass' ) );
		});

		// add 2 classes to 3 els
		test( 'Add multiple classes to multiple el', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el, 'test-byClass', 'test-byClass-2' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass-2' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass-2' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass-2' ) );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-byClass' ) );

			removeClass( el, 'test-byClass', 'test-byClass-2' );

		});

		test( 'Has multiple class multiple elements', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el, 'test-byClass' );
			expect( hasClass( el, 'test-byClass', 'test-byClass-2' ) ).toEqual( false );
			removeClass( el, 'test-byClass' );

			addClass( el, 'test-byClass-mult', 'test-byClass-2-mult' );
			expect( hasClass( el, 'test-byClass-mult', 'test-byClass-2-mult' ) ).toEqual( true );
			expect( hasClass( el, 'test-byClass-mult', 'test-random' ) ).toEqual( false );
			expect( hasClass( el, 'test-random', 'test-byClass-2-mult' ) ).toEqual( false );
			removeClass( el, 'test-byClass-mult', 'test-byClass-2-mult' );

		});

		// remove 2 classes to 3 els
		test( 'Remove multiple classes from multiple el', () => {

			let el = byClass( 'outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el, 'test-byClass', 'test-byClass-2' );
			removeClass( el, 'test-byClass', 'test-byClass-2' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass-2' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass-2' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass-2' ) );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-byClass' ) );
		});

	});

	// queryAll
	describe( 'queryAll()', () => {

		// add 1 class
		test( 'Add single class to single el', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el[0], 'test-queryAll' );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll' ) );
			removeClass( el[0], 'test-queryAll' );

		});

		test( 'Has class', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el[0], 'test-queryAll' );
			expect( hasClass( el[0], 'test-queryAll' ) ).toEqual( true );
			expect( hasClass( el[0], 'test-random' ) ).toEqual( false );
			removeClass( el[0], 'test-queryAll' );

		});

		// remove 1 class
		test( 'Remove single class from single el', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el[0], 'test-queryAll' );
			removeClass( el[0], 'test-queryAll' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll' ) );
		});

		// add 1 class to 3 els
		test( 'Add single class to multiple el', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el, 'test-queryAll' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll' ) );

			removeClass( el, 'test-queryAll' );

		});

		test( 'Has single class multiple elements', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el[0], 'test-queryAll' );

			expect( hasClass( el, 'test-queryAll' ) ).toEqual( false );
			expect( hasClass( el, 'test-random' ) ).toEqual( false );

			addClass( el, 'test-queryAll-mult' );
			expect( hasClass( el, 'test-queryAll-mult' ) ).toEqual( true );

			removeClass( el, 'test-queryAll', 'test-queryAll-mult' );

		});

		// remove 1 class to 3 els
		test( 'Remove single class from multiple el', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el, 'test-queryAll' );
			removeClass( el, 'test-queryAll' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll' ) );
		});

		// add 2 classes
		test( 'Add multiple classes to single el', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el[0], 'test-queryAll', 'test-queryAll-2' );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll-2' ) );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll' ) );
			removeClass( el[0], 'test-queryAll', 'test-queryAll-2' );

		});

		test( 'Has class multiple', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el[0], 'test-queryAll', 'test-queryAll-2' );
			expect( hasClass( el[0], 'test-queryAll', 'test-queryAll-2' ) ).toEqual( true );
			expect( hasClass( el[0], 'test-queryAll', 'test-random' ) ).toEqual( false );
			expect( hasClass( el[0], 'test-random', 'test-queryAll-2' ) ).toEqual( false );
			removeClass( el[0], 'test-queryAll', 'test-queryAll-2' );

		});

		// remove 2 classes
		test( 'Remove multiple classes from single el', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el[0], 'test-queryAll', 'test-queryAll-2' );
			removeClass( el[0], 'test-queryAll', 'test-queryAll-2' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll-2' ) );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll' ) );
		});

		// add 2 classes to 3 els
		test( 'Add multiple classes to multiple el', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el, 'test-queryAll', 'test-queryAll-2' );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll-2' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll-2' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll-2' ) );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.stringContaining( 'test-queryAll' ) );
			removeClass( el, 'test-queryAll', 'test-queryAll-2' );

		});

		test( 'Has multiple class multiple elements', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );

			addClass( el, 'test-queryAll' );
			expect( hasClass( el, 'test-queryAll', 'test-queryAll-2' ) ).toEqual( false );
			removeClass( el, 'test-queryAll' );

			addClass( el, 'test-queryAll-mult', 'test-queryAll-2-mult' );
			expect( hasClass( el, 'test-queryAll-mult', 'test-queryAll-2-mult' ) ).toEqual( true );
			expect( hasClass( el, 'test-queryAll-mult', 'test-random' ) ).toEqual( false );
			expect( hasClass( el, 'test-random', 'test-queryAll-2-mult' ) ).toEqual( false );
			removeClass( el, 'test-queryAll-mult', 'test-queryAll-2-mult' );

		});

		// remove 2 classes to 3 els
		test( 'Remove multiple classes from multiple el', () => {

			let el = queryAll( '.outer' );

			expect( el.length ).not.toBe( 0 );
			addClass( el, 'test-queryAll', 'test-queryAll-2' );
			removeClass( el, 'test-queryAll', 'test-queryAll-2' );

			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll-2' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll-2' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll-2' ) );
			expect( el[0].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll' ) );
			expect( el[1].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll' ) );
			expect( el[2].getAttribute( 'class' ) ).toEqual( expect.not.stringContaining( 'test-queryAll' ) );
		});

	});

});