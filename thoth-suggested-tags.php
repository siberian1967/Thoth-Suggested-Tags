<?php
/*
Plugin Name: Thoth's Suggested Tags
Plugin URI: http://wiki.github.com/edlab/Thoth-Suggested-Tags/
Description: Recommends tags in a tag cloud based on post content as well as any existing tags in the database.
Tags in arrays are associated to a "tag strength", an integer that measures how appropriate the tag is to recommend based on the post content. This value is determined by the word count of the tag, its frequency in the post, and its count in the wordpress database (number of times it has been tagged in other posts).
Version: 1.0
Author: Jimmy O'Higgins
*/

if(is_dir(WPMU_PLUGIN_DIR . '/toth-suggested-tags'))
	define('TOTH_INCLUDES', WPMU_PLUGIN_URL . '/toth-suggested-tags');
else
	define('TOTH_INCLUDES', WP_PLUGIN_URL . '/toth-suggested-tags');

//These words cannot be at the beginning or end of any tags
$stop_words = str_replace(",", " ", " a,&amp,able,about,across,after,all,almost,also,am,among,an,and,any,are,arent,as,at,be,because,between,been,began,both,but,by,can,cannot,could,dear,did,do,does,doesnt,dont,either,else,ever,every,for,from,gave,get,got,had,has,have,he,her,here,hers,him,his,how,however,i,if,in,into,instead,is,it,its,just,least,let,like,likely,many,may,me,might,most,more,must,my,neither,no,nor,not,of,off,often,on,only,or,other,our,own,rather,said,say,says,shall,she,should,since,so,some,take,than,that,the,their,them,then,there,theres,these,they,this,tis,to,too,twas,us,wants,was,we,were,what,when,where,which,while,who,whom,why,will,with,would,yet,you,your ");

function add_box()
{
	add_meta_box('boxid',
				 'Suggested Tags',
				 'box_routine',
				 'post',
				 'normal',
				 'high');
}

function box_routine()
{//Generates a tag cloud from tag list
	$limit = 15;
	$tags_post = tag_list_generate_post();
	$tags_db = tag_list_generate_db();
	$tags_attach = tag_list_generate_attach();
	
	//No tags from post
	if(empty($tags_post))
	{
		echo "Click 'Save Draft' to refresh tag suggestions.";
		return;
	}
	
	$tags_rec = $tags_post;
	
	//Merge with attachment tag recommendations
	if(!empty($tags_attach))
	{
		$tags_rec = array_merge($tags_rec, $tags_attach);
		
	}
	
	//If tag exists in database, double its strength and add its database count.
	foreach($tags_rec as $tag_name => &$tag_strength)
	{
		if(array_key_exists($tag_name, $tags_db))
		{
			$tag_strength *= 2;
			$tag_strength += $tags_db[$tag_name];
		}
	}
	arsort($tags_rec);
	array_splice($tags_rec, $limit);
	
	//Init tag cloud variables
	$min_size = 10;
	$max_size = 24;
	
	$minimum_strength = min(array_values($tags_rec));
	$maximum_strength = max(array_values($tags_rec));
	
	$spread = $maximum_strength - $minimum_strength;
	if($spread == 0) $spread = 1;
	
	$step = ($max_size - $min_size)/($spread);
	
	//Print tag cloud
	foreach($tags_rec as $tag_name => $tag_strength)
	{
		$size = $min_size + ($tag_strength - $minimum_strength) * $step;
		?>
		<a href="#" style="font-size: <?php echo "$size"?>pt;" onClick="tag_add('<?php echo $tag_name; ?>');return false;"><?php echo "$tag_name"?></a>
		<?php
		echo "&nbsp&nbsp&nbsp";
	}
}

function tag_list_generate_db()
{//Generates recommended tags from existing tags in the database
	global $post, $stop_words;
	
	//Get tags from database
	$tags = get_terms('post_tag', "get=all");
	$tags_rec = array();
	
	//Convert $tags = array of tag structs into $tags_rec = array of $tag_name => $tag_strength
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
 * The main loop in this function is based on a simple genetic sequencing algorithm used to find
 * "k-mers", recurring base patterns of length k in a DNA sequence. Just as biologists want
 * find every possible k-mer and count its frequency, we want to find every phrase and
 * its frequency, using "words" and "content" analogously to the biologists' "base pair" and "gene".
 * Google "k-mer counting" if you're curious.
 */
function tag_list_generate_post()
{
	global $post, $stop_words;
	
	$phrase_length_max = 4;
	$phrases = array();
	
	$content = $post->post_title;
	$content .=  " ".$post->post_content;
	$content = strip_tags($content);
	$content = strtolower($content);
	$content = preg_replace('/[\/"’“”\']/', '', $content);
	
	//Split the content at these symbols, which delimit possible tags
	$content_split = preg_split('/[–().,!?—;:…\n]/', $content);
	
	//Begin k-mer loop
	foreach($content_split as $section)
	{
		$content_exploded = explode(" ", $section);
		
		for($phrase_length = 1; $phrase_length < $phrase_length_max; $phrase_length++)
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
				//Phrase built
				

				if((str_word_count($phrase) == $phrase_length))
				{//Evaluate phrase
					$phrase_exploded = explode(" ", $phrase);
					$first_word = trim($phrase_exploded[0]);
					$count = count($phrase_exploded);
					$last_word = trim($phrase_exploded[--$count]);
					
					//Phrase cannot be empty, or begin/end with a stop word
					if(!empty($phrase_exploded)
						&& !stristr($stop_words, $first_word)
						&& !stristr($stop_words, $last_word))
					{//Add phrase
						$phrase = implode(" ", $phrase_exploded);
						$phrase = trim($phrase);
						$phrases[] = $phrase;
					}
				}
			}
		}
	}
	//End k-mer loop
	
	//Multiply tag strength by the tag word count (max: 3)
	$phrases = array_count_values($phrases);
	foreach($phrases as $phrase => &$strength)
	{
		$multiplier = str_word_count($phrase);
		if($multiplier > 3) $multiplier = 3;
		$new_strength = $strength * $multiplier;
		if($new_strength > $multiplier)
		{//Strong tag, multiply
			$strength = $new_strength;
		}
		else
		{//Weak tag, discard
			unset($phrases[$phrase]);
		}
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
	return $phrases;
}

function tag_list_generate_attach()
{
	global $post;
	$content = $post->post_content;
	$tags_rec = array();
	$video_count = 0;
	$video_strength = 4;
	
	//Array of strings to associate with video
	$video_strings = array('http://www.youtube.com/', 'http://vimeo.com/', 'http://www.dailymotion.com/', 'http://video.google.com/', '.avi', '.divx', '.flv', '.m4v', '.mov', '.mp4', '.mkv', '.mpg', '.ogm', '.swf', '.vob', '.wmv', '.xvid');
	
	//Array of strings to associate with audio
	$audio_strings = array('.aac', '.aif', '.iff', '.m3u', '.mid', '.midi', '.mp3', '.mpa', '.wav', '.wma');
	
	//Search for video-associated strings in post
	foreach($video_strings as $video_type)
	{
		$video_count += substr_count($content, $video_type);
	}
	
	if($video_count)
	{
		$tags_rec['video'] = $video_count * $video_strength;
	}
	
	return $tags_rec;
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

function admin_add_my_script()
{
	wp_enqueue_script('thoth-add-tag', TOTH_INCLUDES . '/thoth-add-tag.js', array('jquery'));
}

if(is_admin())
{
	add_action('admin_menu', 'add_box');
	add_action('admin_print_scripts', 'admin_add_my_script');
}
?>