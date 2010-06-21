<?php
/*
Plugin Name: Rec Tags Redux
Plugin URI: #
Description: Recommends Tags based on post content as well as any existing tags.
Plugin logic based on CyberNet's "Recommended Tags" plugin for WP 2.7.
Version: 0.3
Author: Jimmy O'Higgins
*/

function add_box()
{
	add_meta_box('boxid',
				 'Rec Tags Redux',
				 'box_routine',
				 'post',
				 'normal',
				 'high');
}

function box_routine()
{
	$wordfreq = tag_list_generate_post();
	$tags_db = tag_list_generate_db();
	
	//$tags = $wordfreq;
	//array_push($tags, $tags_db);
	
	//Print elements of wordfreq
	$i = 0;
	$limit = 20;
	foreach($wordfreq as $key => $value)
	{
		if($i++ == $limit) break;
		echo "$key => $value <br/>\n";
	}
	//Print elements of tags_db
	
	foreach($tags_db as $tag)
	{
		echo "$tag <br/>\n";
	}
	
}

function tag_list_generate_db()
{//Generates tags from existing tags in the database
	global $post, $wpdb, $post_ID;
	
	$tags = get_terms('post_tag', "get=all");
	foreach($tags as &$tag_object)
	{
		$tag_object = $tag_object->name;
	}
	
	if($tags)
	{
		//Initialize
		$tags_recommended = "";
		$tags_all = "";
		$tags_checked = array("");
		
		//Retrieve post
		$content = strip_tags($wpdb->get_var("SELECT post_content FROM $wpdb->posts WHERE ID = '$post_ID' LIMIT 1"));
		$content .= $post->post_title;
		//$content = preg_replace();
		
		foreach($tags as $tag)
		{
			if(stristr($content, $tag))
			{
				array_push($tags_checked, $tag);
			}
		}
		return $tags_checked;
	}
}

function tag_list_generate_post()
{//Generates tags from post content (word frequency)
	global $post;
	$content =  $post->post_content;
	$content .= " ".$post->post_title;
	$content = strtolower($content);
	$wordfreq = array_count_values(str_word_count($content, 1, '0'));
	arsort($wordfreq);
	
	return $wordfreq;
}

/*
function generate_tag_list()
{//Combines tag lists from database and post data
	
}
*/

if(is_admin())
{
	add_action('admin_menu', 'add_box');
}

?>