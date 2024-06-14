(function (blocks, element, serverSideRender, blockEditor) {
  var el = element.createElement,
    registerBlockType = blocks.registerBlockType,
    ServerSideRender = serverSideRender,
    useBlockProps = blockEditor.useBlockProps;

  registerBlockType("draw-attention/image", {
    apiVersion: 2,
    title: "Draw Attention Image",
    icon: "images-alt2",
    category: "widgets",
    example: {},

    edit: function (props) {
      var blockProps = useBlockProps();
      return el(
        "div",
        blockProps,
        el(ServerSideRender, {
          block: "draw-attention/image",
          attributes: props.attributes,
        }),
      );
    },
  });
})(
  window.wp.blocks,
  window.wp.element,
  window.wp.serverSideRender,
  window.wp.blockEditor,
);
