<?php

/**
 * Force Gutenberg editor canvas to a minimal height on pages.
 */
function dt_admin_collapse_editor_canvas($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    wp_register_script('dt-admin-editor-hack', false, ['wp-data'], null, true);
    wp_enqueue_script('dt-admin-editor-hack');
    wp_add_inline_script(
        'dt-admin-editor-hack',
        "(function(){\n"
        . "  if (!document.body.classList.contains('block-editor-page')) {\n"
        . "    return;\n"
        . "  }\n"
        . "  var styleId = 'dt-admin-editor-hack-style';\n"
        . "  function injectStyle(){\n"
        . "    if (document.getElementById(styleId)) {\n"
        . "      return;\n"
        . "    }\n"
        . "    var style = document.createElement('style');\n"
        . "    style.id = styleId;\n"
        . "    style.textContent = ''\n"
        . "      + 'body.block-editor-page .edit-post-layout__content{padding-top:0;display:flex;flex-direction:column;}'\n"
        . "      + 'body.block-editor-page .edit-post-layout__content .editor-styles-wrapper::after{content:none !important;height:0 !important;display:none !important;}'\n"
        . "      + 'body.block-editor-page .edit-post-layout__metaboxes{margin-top:0;flex:1 1 auto;}'\n"
        . "      + 'body.block-editor-page #postdivrich,body.block-editor-page #post-status-info{display:none;}'\n"
        . "      + 'body.block-editor-page #post-body-content{margin-bottom:0;}';\n"
        . "    document.head.appendChild(style);\n"
        . "  }\n"
        . "  function apply(){\n"
        . "    var container = document.querySelector('.components-resizable-box__container.editor-resizable-editor');\n"
        . "    if (container) {\n"
        . "      container.style.height = '1px';\n"
        . "      container.style.minHeight = '1px';\n"
        . "      container.style.maxHeight = '1px';\n"
        . "      container.style.flex = '0 0 1px';\n"
        . "      container.style.overflow = 'hidden';\n"
        . "      var inner = container.firstElementChild;\n"
        . "      if (inner) {\n"
        . "        inner.style.height = '1px';\n"
        . "        inner.style.minHeight = '1px';\n"
        . "        inner.style.maxHeight = '1px';\n"
        . "        inner.style.overflow = 'hidden';\n"
        . "      }\n"
        . "    }\n"
        . "    var visual = document.querySelector('.editor-visual-editor, .edit-post-visual-editor');\n"
        . "    if (visual) {\n"
        . "      visual.style.height = '1px';\n"
        . "      visual.style.minHeight = '1px';\n"
        . "      visual.style.maxHeight = '1px';\n"
        . "      visual.style.overflow = 'hidden';\n"
        . "    }\n"
        . "  }\n"
        . "  var raf = null;\n"
        . "  function schedule(){\n"
        . "    if (raf) {\n"
        . "      return;\n"
        . "    }\n"
        . "    raf = requestAnimationFrame(function(){\n"
        . "      raf = null;\n"
        . "      injectStyle();\n"
        . "      apply();\n"
        . "    });\n"
        . "  }\n"
        . "  schedule();\n"
        . "  var observer = new MutationObserver(schedule);\n"
        . "  observer.observe(document.body, { childList: true, subtree: true, attributes: true });\n"
        . "  if (window.wp && wp.data && wp.data.subscribe) {\n"
        . "    var last = 0;\n"
        . "    wp.data.subscribe(function(){\n"
        . "      var now = Date.now();\n"
        . "      if (now - last < 200) {\n"
        . "        return;\n"
        . "      }\n"
        . "      last = now;\n"
        . "      schedule();\n"
        . "    });\n"
        . "  }\n"
        . "})();\n"
    );
}
add_action('admin_enqueue_scripts', 'dt_admin_collapse_editor_canvas');
