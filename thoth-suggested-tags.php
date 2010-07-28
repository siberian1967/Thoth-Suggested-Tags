<?php
/*
Plugin Name: Thoth's Suggested Tags
Plugin URI: http://wiki.github.com/edlab/Thoth-Suggested-Tags/
Description: Recommends tags in a tag cloud based on post content as well as any existing tags in the database.
Version: 1.3
Author: Jimmy O'Higgins
*/

//TODO
//Citations

if(is_dir(WPMU_PLUGIN_DIR . '/thoth-suggested-tags'))
	define('THOTH_INCLUDES', WPMU_PLUGIN_URL . '/thoth-suggested-tags');
else
	define('THOTH_INCLUDES', WP_PLUGIN_URL . '/thoth-suggested-tags');

//These words cannot be at the beginning or end of any tags
$stop_words = str_replace(",", " ", " a,&amp,after,almost,also,am,among,an,and,any,are,aren't,as,at,be,because,between,been,began,both,but,by,can,cannot,could,dear,did,do,does,doesn't,don't,either,else,ever,every,for,from,gave,get,got,had,has,have,he,her,here,hers,him,his,how,however,i,if,in,into,instead,is,it,its,it's,least,let,like,likely,many,may,me,might,most,more,must,my,neither,nor,of,off,often,on,only,or,other,our,own,rather,really,said,say,says,shall,she,should,since,so,some,take,than,that,the,their,them,then,there,there's,these,they,this,tis,to,too,twas,us,wants,was,we,were,what,when,where,which,while,who,whom,why,will,with,would,yet,you,you'll,your,those ");

//These words cannot be tags by themselves
$single_words = $stop_words . "i ii iii iv v one two three four five six seven eight nine ten long short up down left right great far near stand eyes hand years time box just no yes big little large small asked placed put happens happen another without someone anything something sometimes enough think much around things type still via over same side new old see call using won lost not case look provide way subject come behind before below above point together longer shorter aboard about above absent across after against along alongside amid amidst among amongst as aside astride at athwart atop barring before behind below beneath beside besides between betwixt beyond but by circa concerning despite down during except excluding failing following for from given in including inside into like mid midst minus near next notwithstanding of off on onto opposite out outside over pace past per plus pro qua regarding round save since than through thru throughout till times to toward towards under underneath unlike until up upon versus via with within without worth ";

function add_box()
{
	add_meta_box('boxid',
				 'Suggested Tags',
				 'box_routine',
				 'post',
				 'side',
				 'low');
}

function box_routine()
{//Generates a tag cloud from tag list
	
	$limit = 12;
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
	
	
	foreach($tags_rec as $tag_name => &$tag_strength)
	{
		if(is_array($tags_db)
			&& !empty($tags_db)
			&& array_key_exists($tag_name, $tags_db))
		{//If tag exists in database, double its strength and add its database count.
			$tag_strength *= 2;
			$tag_strength += $tags_db[$tag_name];
		}
		if($tag_strength < 2)
		{//Discard weak tags
			unset($tags_rec[$tag_name]);
		}
	}
	arsort($tags_rec);
	array_splice($tags_rec, $limit);
	
	//TAG CLOUD
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
		
		if(array_key_exists($tag_name, $tags_db))
		{//Add asterisk to specify database suggestion
			$new_name = $tag_name . '*';
?>
		
			<a href="#" style="font-size: <?php echo "$size"?>pt;" onClick="tag_add('<?php echo $tag_name; ?>');return false;"><?php echo "$new_name"?></a>
			
<?php
		}
		else
		{//No match in database, render tag as is
?>
		
			<a href="#" style="font-size: <?php echo "$size"?>pt;" onClick="tag_add('<?php echo $tag_name; ?>');return false;"><?php echo "$tag_name"?></a>
			
<?php
		}
		//Space between tags
		echo "&nbsp&nbsp&nbsp";
	}
}

function tag_list_generate_db()
{//Generates recommended tags from existing tags in the database
	global $post, $stop_words;
	
	//Get tags from database
	$tags = get_terms('post_tag', "get=all");
	$tags_rec = array();
	
	//Convert $tags = array(tag structs) into $tags_rec = array($tag_name => $tag_strength)
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
		$content = preg_replace('/[–-—]/', ' ', $content);
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
						$pluralized = $word . 's';
						$tags_rec[$word] = 0;
						$tags_rec[$pluralized] = 0;
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
The main loop in this function is based on a simple genetic sequencing algorithm used to find "k-mers", recurring base patterns of length k in a DNA sequence (e.g. AATG). Just as biologists want find every possible k-mer in a gene and count its frequency, we want to find every phrase in the post and its frequency, using "words" and "content" analogously to the biologists' "base pair" and "gene". Google "k-mer counting" if you're curious.
*/
function tag_list_generate_post()
{
	global $post, $stop_words, $single_words;
	
	$phrase_length_max = 4;
	$phrases = array();
	
	//Initialize post content
	$content = $post->post_title;
	$content .=  " ".$post->post_content;
	$content = strip_tags($content);
	$content = preg_replace('/[\/"“”]/', '', $content);
	$content = preg_replace('/[–-—]/', ' ', $content);
	
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
					$last_word = trim($phrase_exploded[$count-1]);
					
					$phrase = implode(" ", $phrase_exploded);
					$phrase = trim($phrase);
					
					//Phrase cannot be empty
					if(!empty($phrase_exploded))
					{
						//Phrase length = 1
						if($count == 1 && !stristr($single_words, $phrase))
						{//Check against "single words" list
							$phrases[] = $phrase;
						}
						//Phrase length = 2
						else if($count == 2
								&& !stristr($stop_words, $first_word)
								&& !stristr($stop_words, $last_word))
						{//Check against "stop words" list
							$phrases[] = $phrase;
						}
						//Phrase length = 3
						else if($count > 2
								&& !stristr($stop_words, $first_word)
								&& !stristr($stop_words, $last_word)
								&& is_proper_phrase($phrase))
						{//Phrase must be proper
							$phrases[] = $phrase;
						}
					}
				}
			}
		}
	}
	//End k-mer loop
	
	//Begin postprocessing
	$phrases = array_count_values($phrases);
	arsort($phrases);
	
	//Discard weak tags
	foreach($phrases as $phrase => $strength)
	{
		if($strength < 2)
			unset($phrases[$phrase]);
	}
	
	foreach($phrases as $phrase => &$strength)
	{
		//For single words
		if(str_word_count($phrase) == 1)
		{
			//Check for lowercase and match
			if(is_proper($phrase))
			{
				$lowercase = lcfirst($phrase);
				$lowercase = trim($lowercase);
				if(array_key_exists($lowercase, $phrases))
				{
					$phrases[$lowercase] += $strength;
					unset($phrases[$phrase]);
				}
			}
			
			//Check for plurals and match
			$pluralized = $phrase.'s';
			$pluralized = trim($pluralized);
			if(array_key_exists($pluralized, $phrases))
			{
				$phrases[$pluralized] += $strength;
				unset($phrases[$phrase]);
			}
			
			//Check for duplicates
			foreach($phrases as $phrase2 => $strength2)
			{
				if(strcasecmp($phrase, $phrase2)
					&& strstr($phrase2, $phrase))
				{
					if($strength == $strength2)
					{
						unset($phrases[$phrase]);
					}
					else if($strength > $strength2)
					{
						$strength -= $strength2;
					}
				}
			}
		}
		//Multiply by phrase length
		$multiplier = str_word_count($phrase);
		if($multiplier > 3) $multiplier = 3;
		$strength *= $multiplier;
	}
	arsort($phrases);
	
	return $phrases;
}

//Check post content for embedded media and recommend media tags
function tag_list_generate_attach()
{
	global $post;
	$content = $post->post_content;
	$tags_rec = array();
	$video_count = 0;
	$audio_count = 0;
	$video_strength = 4;
	$audio_strength = 4;
	
	//Array of strings to associate with video
	$video_strings = array('http://www.youtube.com/', 'http://vimeo.com/', 'http://www.dailymotion.com/', 'http://video.google.com/', '.avi', '.divx', '.flv', '.m4v', '.mov', '.mp4', '.mkv', '.mpg', '.ogm', '.swf', '.vob', '.wmv', '.xvid');
	
	//Array of strings to associate with audio
	$audio_strings = array('.aac', '.aif', '.iff', '.m3u', '.mid', '.midi', '.mp3', '.mpa', '.wav', '.wma');
	
	//Search for video-associated strings in post
	foreach($video_strings as $video_type)
		$video_count += substr_count($content, $video_type);
	
	if($video_count)
		$tags_rec['video'] = $video_count * $video_strength;
	
	foreach($audio_strings as $audio_type)
		$audio_count += substr_count($content, $audio_type);
	
	if($audio_count)
		$tags_rec['audio'] = $audio_count * $audio_strength;
	
	return $tags_rec;
}

function print_exploded($array)
{
	$exploded = $array;
	foreach($exploded as $string)
		echo($string.' ');
	echo ('<br/>');
}

function print_r2($val)
{
	echo '<pre>';
	print_r($val);
	echo '</pre>';
}

function is_proper($string)
{
	$string_split = str_split($string);
	$first_letter = $string_split[0];
	$pattern = '/[A-Z]/';
	$proper = preg_match($pattern, $first_letter);
	if($proper)
		return true;
	else
		return false;
}

function is_proper_phrase($phrase)
{
	$phrase_exploded = explode(" ", $phrase);
	$count = count($phrase_exploded);
	$first_word = trim($phrase_exploded[0]);
	$last_word = trim($phrase_exploded[$count-1]);
	$proper = is_proper($first_word) && is_proper($last_word);
	if($proper)
		return true;
	else
		return false;
}

function admin_add_my_script()
{
	wp_enqueue_script('thoth-add-tag', THOTH_INCLUDES . '/thoth-add-tag.js', array('jquery'));
}

if(is_admin())
{
	add_action('admin_menu', 'add_box');
	add_action('admin_print_scripts', 'admin_add_my_script');
}
?>