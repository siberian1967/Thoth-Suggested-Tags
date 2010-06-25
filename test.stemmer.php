<?php

require_once 'lib/class.stemmer.inc';


# instantiate a stemmer

$stemmer = new Stemmer;


# set the word and stem it

$word = "category";
$stem = $stemmer->stem($word);


# print the results

print "The stem of $word is $stem.\n";


?>