var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.components.ServerSideRender;

registerBlockType( 'draw-attention/image', {
	title: 'Draw Attention Image',
	icon: 'images-alt2',
	category: 'widgets',

	edit: function() {
		return [
			el( ServerSideRender, {
				block: 'draw-attention/image'
			} )
		];
	},

	save: function() {
		return null;
	},
} );