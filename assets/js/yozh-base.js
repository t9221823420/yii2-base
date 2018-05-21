var yozh = {
	options : {},
};

( function ( yozh, $, undefined ) {
	
	/*
	//Private Property
	var isHot = true;

	//Public Property
	yozh.ingredient = "Bacon Strips";

	//Public Method
	yozh.fry = function() {
		var oliveOil;

		addItem( "\t\n Butter \n\t" );
		addItem( oliveOil );
		console.log( "Frying " + yozh.ingredient );
	};

	//Private Method
	function addItem( item ) {
		if ( item !== undefined ) {
			console.log( "Adding " + $.trim(item) );
		}
	}
	*/
	
}( window.yozh = window.yozh || {}, jQuery ) );


function call_user_func( _functionName, _context /*, args */ ) {
	
	_context = _context || window;
	
	var args = Array.prototype.slice.call( arguments, 2 );
	var namespaces = _functionName.split( "." );
	var func = namespaces.pop();
	
	for ( var i = 0; i < namespaces.length; i++ ) {
		_context = _context[ namespaces[ i ] ];
	}
	
	return _context[ func ].apply( _context, args );
}


function strtr( s, p, r ) {
	
	//var s = this.toString();
	
	return !!s && {
		2 : function () {
			for ( var i in p ) {
				s = strtr( s, i, p[ i ] );
			}
			return s;
		},
		3 : function () {
			return s.replace( RegExp( p, 'g' ), r );
		},
		0 : function () {
			return;
		}
	}[ arguments.length ]();
}