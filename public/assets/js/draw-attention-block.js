var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.components.ServerSideRender,
	SelectControl = wp.components.SelectControl,
	InspectorControls = wp.editor.InspectorControls;

registerBlockType( 'draw-attention/image', {
	title: 'Draw Attention Image',
	icon: 'images-alt2',
	category: 'widgets',

	edit: function( props ) {
		var options = [
			{
				value: '',
				label: 'All'
			}
		]
		Object.keys(drawAttentionImages).forEach(function(key) {
			options.push({
				value: key,
				label: drawAttentionImages[key]
			})
		})

		return [
			el( ServerSideRender, {
				block: 'draw-attention/image',
				attributes: props.attributes,
			} ),

			el( InspectorControls, {},
				el( SelectControl, {
					label: 'Image',
					value: props.attributes.id,
					options: options,
					onChange: ( value ) => { props.setAttributes( {id: value } ); },
				} )
			),
		];
	},

	save: function() {
		return null;
	},
} );