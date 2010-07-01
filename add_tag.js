function tag_add(tag)
{
	if (jQuery('#new-tag-post_tag').val() == "") {
		jQuery('#new-tag-post_tag').val(tag);
	} else {
		jQuery('#new-tag-post_tag').val(jQuery('#new-tag-post_tag').val() + ", " + tag);
	}
	jQuery('.tagadd').click();
}