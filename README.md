<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="Content-Style-Type" content="text/css">
  <title></title>
  <meta name="Generator" content="Cocoa HTML Writer">
  <meta name="CocoaVersion" content="949.54">
  <style type="text/css">
    p.p1 {margin: 0.0px 0.0px 0.0px 0.0px; font: 12.0px Helvetica}
    p.p2 {margin: 0.0px 0.0px 0.0px 0.0px; font: 12.0px Helvetica; min-height: 14.0px}
  </style>
</head>
<body>
<p class="p1">Thoth's Tag Suggestions</p>
<p class="p1">===</p>
<p class="p2"><br></p>
<p class="p1">Version 1.0 - July 1, 2010</p>
<p class="p1">Compatible with WordPress 3.0 or higher</p>
<p class="p2"><br></p>
<p class="p1">by Jimmy O'Higgins</p>
<p class="p2"><br></p>
<p class="p1">Introduction</p>
<p class="p1">---</p>
<p class="p2"><br></p>
<p class="p1">Thoth's Tag Suggestions is a WordPress plugin that recommends tags by scanning a post and displaying recurring words and phrases as a tag cloud.</p>
<p class="p2"><br></p>
<p class="p1">Installation</p>
<p class="p1">---</p>
<p class="p2"><br></p>
<p class="p1">Upload the thoth-tag-suggestions folder to your wp-content/plugins folder.</p>
<p class="p2"><br></p>
<p class="p1">Go to the "Plugins" administration panel.</p>
<p class="p2"><br></p>
<p class="p1">Activate Thoth's Suggested Tags</p>
<p class="p2"><br></p>
<p class="p1">Thoth is now with you.</p>
<p class="p2"><br></p>
<p class="p1">How It Works</p>
<p class="p1">---</p>
<p class="p2"><br></p>
<p class="p1">Every time the user saves a draft or updates a post, Thoth</p>
<p class="p2"><br></p>
<p class="p1">+ Splits the post content into chunks delimited by stop-characters.</p>
<p class="p2"><br></p>
<p class="p1">+ Uses a simple [k-mer counting](http://www.google.com/search?q=k-mer+counting "Google search") algorithm to record every possible phrase in the content and its frequency.</p>
<p class="p2"><br></p>
<p class="p1">+ Does post-processing on the phrase list and displays the final list as a tag cloud. For more information consult the "Features" section.</p>
<p class="p2"><br></p>
<p class="p1">Features</p>
<p class="p1">---</p>
<p class="p2"><br></p>
<p class="p1">+ Tag strength (`$strength` in the code) is an integer representing the likelihood of the tag being appropriate to the post. A tag's strength is initially determined by its frequency in the post.</p>
<p class="p2"><br></p>
<p class="p1">+ Stop Words - Filters out phrases beginning or ending with any words in the stop-word list.</p>
<p class="p2"><br></p>
<p class="p1">+ Tag length - Tag strength is multiplied by the number of words in the tag with a maximum of 3. This means that longer recurring phrases are ranked higher than shorter ones.</p>
<p class="p2"><br></p>
<p class="p1">+ Pluralization - For every potential single-word tag, Thoth adds a plural suffix 's' and searches for matches in the potential tag list. If a match is found, the tag strength of the singular is transferred to the plural version (e.g. "download" becomes "downloads"). If a match is not found, the singular is used.</p>
<p class="p2"><br></p>
<p class="p1">+ Existing tags - Thoth also retrieves all the tags used in your blog and searches for instances of them in the content of the post. In the case of a match, the tag strength is multiplied by 2 and incremented by the number of times that tag has been used in your blog. This means that if your blog has a unifying theme, certain tags are likely to be reused and will enable Thoth to make better suggestions.</p>
<p class="p2"><br></p>
<p class="p2"><br></p>
<p class="p1">Version History</p>
<p class="p1">---</p>
<p class="p2"><br></p>
<p class="p1">1.0 (July 1, 2010)</p>
<p class="p2"><br></p>
<p class="p1">* Implemented tag cloud output from array of recommended strings.</p>
<p class="p2"><br></p>
<p class="p1">* Cleaned up code</p>
<p class="p2"><br></p>
<p class="p1">* Improved comments</p>
<p class="p2"><br></p>
<p class="p2"><br></p>
<p class="p1">0.8 (June 30, 2010)</p>
<p class="p2"><br></p>
<p class="p1">* Suggested tags are now hyperlinks calling an external Javascript function to add the tag to the input and click the "add tag" button.</p>
<p class="p2"><br></p>
<p class="p2"><br></p>
<p class="p1">0.7 (June 28, 2010)</p>
<p class="p2"><br></p>
<p class="p1">* Implemented pluralizing, i.e. adding an 's' suffix to each word and checking for matches in existing tag list</p>
<p class="p2"><br></p>
<p class="p2"><br></p>
<p class="p1">0.5 (June 24, 2010)</p>
<p class="p2"><br></p>
<p class="p1">* Implemented removal of all phrases beginning with stop words</p>
<p class="p2"><br></p>
<p class="p1">* Removal of phrases beginning or ending with stop words</p>
<p class="p2"><br></p>
<p class="p1">* Removal of phrases that appear only once.</p>
<p class="p2"><br></p>
<p class="p1">* Now splits text on stop-chars instead of stop-words (much better).</p>
<p class="p2"><br></p>
<p class="p2"><br></p>
<p class="p1">0.4 (June 23, 2010)</p>
<p class="p2"><br></p>
<p class="p1">* Added list of stop words</p>
<p class="p2"><br></p>
<p class="p1">* Splits the text based on stop words</p>
<p class="p2"><br></p>
<p class="p1">* Implemented phrase frequency counter based on k-mer counting algorithm</p>
<p class="p2"><br></p>
<p class="p2"><br></p>
<p class="p1">0.3 (June 21, 2010)</p>
<p class="p2"><br></p>
<p class="p1">* Implemented word frequency counter for single words</p>
<p class="p2"><br></p>
<p class="p1">* Suggest tags if they exist in both the database and the post content</p>
<p class="p2"><br></p>
<p class="p2"><br></p>
<p class="p1">0.2 (June 20, 2010)</p>
<p class="p2"><br></p>
<p class="p1">* Implemented WordPress `add_meta_box()` function to display in the 'new_post' page.</p>
<p class="p2"><br></p>
<p class="p2"><br></p>
<p class="p1">0.1 (June 19, 2010)</p>
<p class="p2"><br></p>
<p class="p1">* Downloaded WordPress and installed dummy plugin file</p>
</body>
</html>
