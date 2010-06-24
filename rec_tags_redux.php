<?php
/*
Plugin Name: Rec Tags Redux
Plugin URI: #
Description: Recommends tags based on post content as well as any existing tags in the database.
Plugin logic based on CyberNet's "Recommended Tags" plugin for WP 2.7.
Version: 0.4
Author: Jimmy O'Higgins
*/

//TODO
/*
Stemming function to compare two strings
Stemming in database recs
Stemming in final recs
Stemming in post recs
STEMMING EVERYWHERE
*/

require("PorterStemmer.php");
ini_set('display_errors',1);
error_reporting(E_ALL);

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
	$indent = "&nbsp&nbsp&nbsp";
	$tags_post = tag_list_generate_post();
	$tags_db = tag_list_generate_db();
	
	//Final recommendations
	echo "Final recommendations<br/>\n";
	$tags_rec = $tags_post;
	foreach($tags_rec as $tag_name => &$tag_strength)
	{
		if(array_key_exists($tag_name, $tags_db))
		{
			$tag_strength *= 2;
			$tag_strength += $tags_db[$tag_name];
		}
	}
	//Print finals
	$i = 0;
	$limit = 15;
	foreach($tags_rec as $tag_name => $tag_strength)
	{
		if($i++ == $limit) break;
		echo "$indent $tag_name => $tag_strength <br/>\n";
	}
	
	
	
	//Print elements of tags_post
	echo "Recommended tags from post<br/>\n";
	$i = 0;
	$limit = 15;
	foreach($tags_post as $phrase => $strength)
	{
		if($i++ == $limit) break;
		echo "$indent $phrase => $strength <br/>\n";
	}
	
	//Print elements of tags_db
	echo "Recommended tags from db<br/>\n";
	foreach($tags_db as $tag_name => $tag_strength)
	{
		echo "$indent $tag_name => $tag_strength <br/>\n";
	}
}

function tag_list_generate_db()
{//Generates tags from existing tags in the database
	global $post, $wpdb, $post_ID;
	
	//Get tags from database
	$tags = get_terms('post_tag', "get=all");
	$tags_rec = array();
	
	//Flatten tag array so it only includes the tag name
	foreach($tags as $tag_object)
	{
		$name = trim($tag_object->name);
		$strength = $tag_object->count;
		$tags_rec[$name] = $strength;
	}
	
	arsort($tags_rec);

	if($tags_rec)
	{	
		$content = $post->post_title;
		$content .=  " ".$post->post_content;
		$content = strip_tags($content);
		$content = strtolower($content);
		$content = preg_replace('/[-",.;—]/', '', $content);
		
		//Evaluate tags
		foreach($tags_rec as $tag_name => $tag_strength)
		{
			//Check for match
			if(!stristr($content, $tag_name))
			{
				unset($tags_rec[$tag_name]);
			}
		}
		//Return only the matches
		return $tags_rec;
	}
}

function tag_list_generate_post()
{
	global $post;
	$content = $post->post_title;
	$content .=  " ".$post->post_content;
	$content = strip_tags($content);
	$content = strtolower($content);
	$content = preg_replace('/[-",.;—]/', '', $content);
	$content_exploded = explode(" ", $content);
	
	echo($content);
	
	$phrases = array();
	
	$stop_words = str_replace(",", " ", " a,able,about,across,after,all,almost,also,am,among,an,and,any,are,as,at,be,because,been,but,by,can,cannot,could,dear,did,do,does,don't,either,else,ever,every,for,from,get,got,had,has,have,he,her,here,hers,him,his,how,however,i,if,in,into,is,it,its,just,least,let,like,likely,may,me,might,most,must,my,neither,no,nor,not,of,off,often,on,only,or,other,our,own,rather,said,say,says,she,should,since,so,some,than,that,the,their,them,then,there,these,they,this,tis,to,too,twas,us,wants,was,we,were,what,when,where,which,while,who,whom,why,will,with,would,yet,you,your ");
	
	for($phrase_length = 1; $phrase_length < 4; $phrase_length++)
	{
		for($phrase_start = 0; $phrase_start < count($content_exploded); $phrase_start++)
		{
			$phrase = '';
			for($word = 0; $word < $phrase_length; $word++)
			{
				$position = $phrase_start+$word;
				if(array_key_exists($position, $content_exploded))
				{//Build phrase
					$phrase .= $content_exploded[$position]." ";
				}
			}
			
			$phrase = trim($phrase);
			
			if(!(str_word_count($phrase) < $phrase_length))
			{
				$phrase_exploded = explode(" ", $phrase);
				$first_word = trim($phrase_exploded[0]);
				
				if(!empty($phrase_exploded)
					&& !stristr($stop_words, $first_word))
				{
					$phrase = implode(" ", $phrase_exploded);
					
					//echo "adding phrase $phrase to array<br/>";
					$phrase = trim($phrase);
					array_push($phrases, $phrase);
				}
			}
		}
	}
	
	$phrases = array_count_values($phrases);
	
	foreach($phrases as $phrase => &$strength)
	{
		$multiplier = str_word_count($phrase);
		//$multiplier = 1;
		$strength *= $multiplier;
	}
	
	arsort($phrases);
	
	return $phrases;
}

/*
function generate_tag_list()
{//Combines tag lists from database and post data
	
}
*/

function print_exploded($array)
{
	$exploded = $array;
	foreach($exploded as $string)
	{
		echo($string.' ');
	}
	echo ('<br/>');
}

function print_r2($val)
{
	echo '<pre>';
	print_r($val);
	echo '</pre>';
}

if(is_admin())
{
	add_action('admin_menu', 'add_box');
}

?>