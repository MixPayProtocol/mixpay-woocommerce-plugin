( function( $ ) {
    $( document ).ready( function() {
        $( '#radio-control-wc-payment-method-options-mixpay' ).on( 'click', function( e ) {
            e.preventDefault();

            window.open( 'https://mixpay.me', '_blank' )
        } );
    } );
} )( jQuery );