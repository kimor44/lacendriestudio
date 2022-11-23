jQuery(function ($) {
  const wp_inline_edit_function = inlineEditPost.edit;

  // we overwrite the it with our own
  inlineEditPost.edit = function (post_id) {
    // let's merge arguments of the original function
    wp_inline_edit_function.apply(this, arguments);

    // get the post ID from the argument
    if (typeof post_id == "object") {
      // if it is object, get the ID number
      post_id = parseInt(this.getId(post_id));
    }

    // add rows to variables
    const edit_row = $("#edit-" + post_id);
    const post_row = $("#post-" + post_id);

    const visibilityState =
      "oui" == $(".column-visible", post_row).text() ? true : false;

    // populate the input with column data
    $(':input[name="is_visible"]', edit_row).prop("checked", visibilityState);
  };
});
