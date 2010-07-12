=== Thoth's Suggested Tags ===

Contributors: Jimmy O'Higgins
Tags: tags, tag cloud, suggestion
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 1.0

Thoth's Suggested Tags is a WordPress plugin that recommends tags by scanning a post and displaying recurring words and phrases as a tag cloud.

== Description ==

Thoth scans the text for tags are and associates them to a "tag strength", an integer that represents how appropriate the tag is to recommend based on the post content. This value is determined by the word count of the tag, its frequency in the post, and its count in the wordpress database (number of times it has been tagged in other posts).

= How It Works =

Every time the user saves a draft or updates a post, Thoth

+ Splits the post content into chunks delimited by stop-characters.

+ Uses a simple [k-mer counting](http://www.google.com/search?q=k-mer+counting "Google search") algorithm to record every possible phrase in the content and its frequency.

+ Does post-processing on the phrase list and displays the final list as a tag cloud. For more information consult the "Features" section.

= Features =

+ Tag strength (`$strength` in the code) is an integer representing the likelihood of the tag being appropriate to the post. A tag's strength is initially determined by its frequency in the post.

+ Stop Words - Filters out phrases beginning or ending with any words in the stop-word list.

+ Tag length - Tag strength is multiplied by the number of words in the tag with a maximum of 3. This means that longer recurring phrases are ranked higher than shorter ones.

+ Pluralization - For every potential single-word tag, Thoth adds a plural suffix 's' and searches for matches in the potential tag list. If a match is found, the tag strength of the singular is transferred to the plural version (e.g. "download" becomes "downloads"). If a match is not found, the singular is used.

+ Existing tags - Thoth also retrieves all the tags used in your blog and searches for instances of them in the content of the post. In the case of a match, the tag strength is multiplied by 2 and incremented by the number of times that tag has been used in your blog. This means that if your blog has a unifying theme, certain tags are likely to be reused and will enable Thoth to make better suggestions.

== Installation ==

Upload the `thoth-tag-suggestions` folder to your `wp-content/plugins` folder.

Go to the "Plugins" administration panel.

Activate Thoth's Suggested Tags

Thoth is now with you.


== Version History ==

1.0 (July 1, 2010)

* Implemented tag cloud output from array of recommended strings.

* Cleaned up code

* Improved comments


0.8 (June 30, 2010)

* Suggested tags are now hyperlinks calling an external Javascript function to add the tag to the input and click the "add tag" button.


0.7 (June 28, 2010)

* Implemented pluralizing, i.e. adding an 's' suffix to each word and checking for matches in existing tag list


0.5 (June 24, 2010)

* Implemented removal of all phrases beginning with stop words

* Removal of phrases beginning or ending with stop words

* Removal of phrases that appear only once.

* Now splits text on stop-chars instead of stop-words (much better).


0.4 (June 23, 2010)

* Added list of stop words

* Splits the text based on stop words

* Implemented phrase frequency counter based on k-mer counting algorithm


0.3 (June 21, 2010)

* Implemented word frequency counter for single words

* Suggest tags if they exist in both the database and the post content


0.2 (June 20, 2010)

* Implemented WordPress `add_meta_box()` function to display in the 'new_post' page.


0.1 (June 19, 2010)

* Downloaded WordPress and installed dummy plugin file
