<?php
/*
Plugin Name: Rec Tags Redux
Plugin URI: #
Description: Recommends Tags based on post content as well as any existing tags.
Plugin logic based on CyberNet's "Recommended Tags" plugin for WP 2.7.
Version: 0.3
Author: Jimmy O'Higgins
*/

require("PorterStemmer.php");

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
	echo "Word Frequency table<br/>\n";
	$i = 0;
	$limit = 15;
	foreach($wordfreq as $key => $value)
	{
		if($i++ == $limit) break;
		echo "&nbsp $key => $value <br/>\n";
	}
	
	//Print elements of tags_db
	echo "Recommended tags<br/>\n";
	foreach($tags_db as $tag)
	{
		echo "&nbsp $tag <br/>\n";
	}
	$word = 'test';
	$test = PorterStemmer::Stem($word);
	echo($test);
	?>
	<a style="font-size: 15pt;" title="5 topics" class="tag-link-10" href="#">gettysburg</a>
	<?php
}

function tag_list_generate_db()
{//Generates tags from existing tags in the database
	global $post, $wpdb, $post_ID;
	
	//Get tags from database
	$tags = get_terms('post_tag', "get=all");
	//Flatten tag array so it only includes the tag name
	foreach($tags as &$tag_object)
	{
		$tag_object = $tag_object->name;
	}
	
	if($tags)
	{
		$tags_rec = array();
		
		//Retrieve post
		$content = strip_tags($wpdb->get_var("SELECT post_content FROM $wpdb->posts WHERE ID = '$post_ID' LIMIT 1"));
		$content .= $post->post_title;
		//$content = preg_replace();
		
		//Evaluate tags
		foreach($tags as $tag)
		{
			//Check for match
			if(stristr($content, $tag))
			{
				array_push($tags_rec, $tag);
			}
		}
		//Return only the matches
		return $tags_rec;
	}
}

function tag_list_generate_post()
{//Generates tags from post content (word frequency)
	global $post;
	$content =  $post->post_content;
	$content .= " ".$post->post_title;
	$content = strtolower($content);
	$content = preg_replace('/[,.;]/', '', $content);

	$stop_words = "/(".str_replace(",", " | ", "a,able,about,across,after,all,almost,also,am,among,an,and,any,are,as,at,be,because,been,but,by,can,cannot,could,dear,did,do,does,either,else,ever,every,for,from,get,got,had,has,have,he,her,here,hers,him,his,how,however,i,if,in,into,is,it,its,just,least,let,like,likely,may,me,might,most,must,my,neither,no,nor,not,of,off,often,on,only,or,other,our,own,rather,said,say,says,she,should,since,so,some,than,that,the,their,them,then,there,these,they,this,tis,to,too,twas,us,wants,was,we,were,what,when,where,which,while,who,whom,why,will,with,would,yet,you,your").")/";
	
	echo($stop_words);
	
	$content_stopped = preg_split($stop_words, $content);
	//$content_stopped = preg_replace('/[^\w-]+/', '', $content_stopped);
	
	foreach($content_stopped as $string)
	{
		echo "$string <br/>\n";
	}
	
	
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