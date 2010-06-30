<?php
/*
Plugin Name: Rec Tags Redux
Plugin URI: #
Description: Recommends tags based on post content as well as any existing tags in the database.
Tags in arrays are always associated to a "tag strength", an integer that measures how appropriate
the tags is to recommend based on the post content. This value is determined by the word count of the tag,
its frequency in the post, and its count in the wordpress database (number of times it has been tagged in
other posts).
Version: 0.7
Author: Jimmy O'Higgins
*/

//TODO
//add uploaded file format type

ini_set('display_errors',1);
error_reporting(E_ALL);

//These words cannot be at the beginning or end of any tags
$stop_words = str_replace(",", " ", " a,able,about,across,after,all,almost,also,am,among,an,and,any,are,arent,as,at,be,because,between,been,both,but,by,can,cannot,could,dear,did,do,does,don't,dont,either,else,ever,every,for,from,get,got,had,has,have,he,her,here,hers,him,his,how,however,i,if,in,into,is,it,its,just,least,let,like,likely,may,me,might,most,must,my,neither,no,nor,not,of,off,often,on,only,or,other,our,own,rather,said,say,says,shall,she,should,since,so,some,than,that,the,their,them,then,there,these,they,this,tis,to,too,twas,us,wants,was,we,were,what,when,where,which,while,who,whom,why,will,with,would,yet,you,your ");

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
	$tags_rec = $tags_post;
	foreach($tags_rec as $tag_name => &$tag_strength)
	{
		if(array_key_exists($tag_name, $tags_db))
		{
			$tag_strength *= 2;
			$tag_strength += $tags_db[$tag_name];
		}
	}
	arsort($tags_rec);
	
	if(!empty($tags_rec))
	{
		//Print finals
		$i = 0;
		$limit = 15;
		foreach($tags_rec as $tag_name => $tag_strength)
		{
			if($i++ == $limit) break;
			$tag_length = str_word_count($tag_name);
			if($tag_strength > $tag_length)
			{
				//echo "$indent $tag_name => $tag_strength <br/>";
				?>
				<script>
				var tag = <?= json_encode($tag_name); ?>;
				</script>
				<a href="#" onClick="tag_add(tag)"><?php echo "$tag_name => $tag_strength<br/>"?></a>
				<?php
			}
		}
	}
	else
	{
		echo "Save draft to refresh suggested tag list<br/>";
	}
	
	/*
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
	*/
}

function tag_list_generate_db()
{//Generates recommended tags from existing tags in the database
	global $post, $stop_words;
	
	//Get tags from database
	$tags = get_terms('post_tag', "get=all");
	$tags_rec = array();
	
	//Convert array of tag structs ($tags) into array of $tag_name => $tag_strength ($tags_rec)
	foreach($tags as $tag_object)
	{
		$name = trim($tag_object->name);
		$strength = $tag_object->count;
		$tags_rec[$name] = $strength;
	}
	
	arsort($tags_rec);

	if($tags_rec)
	{//Initialize post content
		$content = $post->post_title;
		$content .=  " ".$post->post_content;
		$content = strip_tags($content);
		$content = strtolower($content);
		$content = preg_replace('/[",.;]/', '', $content);
		$content = preg_replace('/[-—]/', ' ', $content);
		$content_exploded = explode(" ", $content);
		$content_stemmed = array();
		
		//Explode db tags so that they match with partials in the post
		foreach($tags_rec as $tag_name => $tag_strength)
		{
			if(str_word_count($tag_name) > 1)
			{
				$exploded = explode(" ", $tag_name);
				foreach($exploded as $word)
				{
					if(!stristr($stop_words, $word))
					{
						$tags_rec[$word] = 0;
					}
				}
			}
		}
		
		//Evaluate tags
		foreach($tags_rec as $tag_name => $tag_strength)
		{
			//Match tags to post content
			if(!stristr($content, $tag_name))
			{
				unset($tags_rec[$tag_name]);
			}
		}
		//Return only the matches
		return $tags_rec;
	}
}

/*
 * This function is based on a simple genetic sequencing algorithm used to find
 * "k-mers", recurring base patterns of length k in a DNA sequence. Just as biologists want
 * find every possible k-mer and count its frequency, we want to find every phrase and
 * its frequency, using "words" analogously to the biologists' "base pair". Google
 * "k-mer counting" if you're curious.
 */
function tag_list_generate_post()
{
	global $post, $stop_words;
	
	$k = 5;
	$phrases = array();
	
	$content = $post->post_title;
	$content .=  " ".$post->post_content;
	$content = strip_tags($content);
	$content = strtolower($content);
	$content = preg_replace('/[\/"’“”\']/', '', $content);
	
	//Split the content at these symbols, which a tag will never contain
	$content_split = preg_split('/[–().,!?—;:…\n]/', $content);
	
	//Begin k-mer loop
	foreach($content_split as $section)
	{
		$content_exploded = explode(" ", $section);
		//$content_exploded = preg_split('/\W+/', $section);
		
		for($phrase_length = 1; $phrase_length < $k; $phrase_length++)
		{
			for($phrase_start = 0; $phrase_start < count($content_exploded); $phrase_start++)
			{
				$phrase = '';
				for($word = 0; $word < $phrase_length; $word++)
				{//Build phrase
					$position = $phrase_start+$word;
					if(array_key_exists($position, $content_exploded))
					{
						$phrase .= $content_exploded[$position]." ";
					}
				}
				$phrase = trim($phrase);
				
				if((str_word_count($phrase) == $phrase_length))
				{
					$phrase_exploded = explode(" ", $phrase);
					$first_word = trim($phrase_exploded[0]);
					$count = count($phrase_exploded);
					$last_word = trim($phrase_exploded[--$count]);
					
					if(!empty($phrase_exploded)
						&& !stristr($stop_words, $first_word)
						&& !stristr($stop_words, $last_word))
					{
						$phrase = implode(" ", $phrase_exploded);
						$phrase = trim($phrase);
						$phrases[] = $phrase;
						
					}
				}
			}
		}
	}
	
	//Multiply tag strength by the tag word count (max: 3)
	$phrases = array_count_values($phrases);
	foreach($phrases as $phrase => &$strength)
	{
		$multiplier = str_word_count($phrase);
		if($multiplier > 3) $multiplier = 3;
		$strength *= $multiplier;
	}
	
	arsort($phrases);
	
	//Check for plurals and match
	foreach($phrases as $phrase => &$strength)
	{
		if(str_word_count($phrase) == 1)
		{
			$pluralized = $phrase.'s';
			$pluralized = trim($pluralized);
			if(array_key_exists($pluralized, $phrases))
			{
				$phrases[$pluralized] += $strength;
				unset($phrases[$phrase]);
			}
		}
	}
	
	//print_r2($phrases);
	
	return $phrases;
}

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

function admin_add_script()
{
	$plugindir = get_settings('home').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));
	wp_enqueue_script('test', $plugindir . '/add_tag.js');
}

if(is_admin())
{
	add_action('admin_menu', 'add_box');
	add_action('admin_print_scripts', 'admin_add_script');
}
?>