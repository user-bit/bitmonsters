# matt-utils
Some simple shorthands for some js functions and methods

## Install

Use npm:

```
npm i -D matt-utils
```

Or pnpm

```
pnpm i -D matt-utils
```

## Usage

Just import the needed functions into your file

```
import { addEvent, byId } from 'matt-utils'

addEvent( byId( 'some-selector' ), 'click', ( ev ) => {
  console.log( 'click' );
});
```

## Functions

Check the [API documentation](api.md).