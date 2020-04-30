/**
 * WordPress dependencies.
 */

const { addFilter } = wp.hooks;

/**
 * Use the 'woocommerce_admin_notices_to_show' filter to add notices to show.
 */
addFilter( 'woocommerce_admin_notices_to_show', 'plugin-domain', notices => {
	return [
        ...notices,
        // element id, [ classes to include ], [ classes to exclude ]
        [ null, [ 'notice-ixwc' ] ],
	];
} );