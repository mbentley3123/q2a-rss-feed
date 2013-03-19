<?php
/*
	Question2Answer RSS feed widget plugin, v0.1
	License: http://www.gnu.org/licenses/gpl.html
*/

class qa_rss_feed
{
	function option_default($option)
	{
		if ( $option == 'rss_feed_title' )
			return 'RSS Feed';
		if ( $option == 'rss_feed_blog_url' )
			return 'http://www.replicon.com/blog';
		if ( $option == 'rss_feed_url' )
			return 'http://www.replicon.com/rss/';
		if ( $option == 'rss_feed_count_posts' )
			return 5;
		if ( $option == 'rss_feed_desc_max_len' )
			return 0;
		if ( $option == 'rss_feed_image_max_width' )
			return 250;
	}

	function admin_form()
	{
		$saved = false;

		if ( qa_clicked('rss_list_save_button') )
		{
			qa_opt( 'rss_feed_title', qa_post_text('rss_feed_title_field') );
			qa_opt( 'rss_feed_blog_url', qa_post_text('rss_feed_blog_url_field') );
			qa_opt( 'rss_feed_url', qa_post_text('rss_feed_url_field') );
			qa_opt( 'rss_feed_count_posts', (int)qa_post_text('rss_feed_count_posts_field') );
			qa_opt( 'rss_feed_desc_max_len', (int)qa_post_text('rss_feed_desc_max_len_field') );
			qa_opt( 'rss_feed_image_max_width', (int)qa_post_text('rss_feed_image_max_width_field') );
			$saved = true;
		}

		return array(
			'ok' => $saved ? 'Tag List settings saved' : null,

			'fields' => array(
				array(
					'label' => 'RSS Feed Title:',
					'type' => 'text',
					'value' => qa_opt('rss_feed_title'),
					'tags' => 'name="rss_feed_title_field"',
				),
				array(
					'label' => 'RSS Feed Title URL:',
					'type' => 'text',
					'value' => qa_opt('rss_feed_blog_url'),
					'tags' => 'name="rss_feed_blog_url_field"',
				),
				array(
					'label' => 'RSS Feed URL:',
					'type' => 'text',
					'value' => qa_opt('rss_feed_url'),
					'tags' => 'name="rss_feed_url_field"',
				),
				array(
					'label' => 'Number of feed posts to show:',
					'type' => 'number',
					'value' => (int)qa_opt('rss_feed_count_posts'),
					'tags' => 'name="rss_feed_count_posts_field"',
				),			
				array(
					'label' => 'Number of characters of text (0 for all) to show:',
					'type' => 'number',
					'value' => (int)qa_opt('rss_feed_desc_max_len'),
					'tags' => 'name="rss_feed_desc_max_len_field"',
				),			
				array(
					'label' => 'Maximum image width (0 for ignore):',
					'type' => 'number',
					'value' => (int)qa_opt('rss_feed_image_max_width'),
					'tags' => 'name="rss_feed_image_max_width_field"',
				),			
			),

			'buttons' => array(
				array(
					'label' => 'Save Changes',
					'tags' => 'name="rss_list_save_button"',
				),
			),
		);
	}

	function allow_template($template)
	{
		switch ($template)
		{
			case 'activity':
			case 'qa':
			case 'questions':
			case 'hot':
			case 'ask':
			case 'categories':
			case 'question':
			case 'tag':
			case 'tags':
			case 'unanswered':
			case 'user':
			case 'users':
			case 'search':
			case 'admin':
				return true;
		}

		return false;
	}

	function allow_region($region)
	{
		return $region == 'side';
	}

	function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
	{
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
		$feed_title = qa_opt('rss_feed_title');
		$feed_blog_url = qa_opt('rss_feed_blog_url');
		$feed_url = qa_opt('rss_feed_url');
		$feed_count = qa_opt('rss_feed_count_posts');
		$feed_desc_max_len = qa_opt('rss_feed_desc_max_len');
		$feed_image_max_width = qa_opt('rss_feed_image_max_width');
		
  	    echo '<H2><a href="'.$feed_blog_url.'">'.$feed_title.'</a></H2>';
			
  	    $rss = new DOMDocument();
		
		$rss->load($feed_url);
		$feed = array();
		foreach ($rss->getElementsByTagName('item') as $node) {
		    try {
                if(isset($node)) {
				    if (isset($node->getElementsByTagName('title')->item(0)->nodeValue)) {
 						$title = $node->getElementsByTagName('title')->item(0)->nodeValue;
					} else {
					$title = '';
					}
				    if (isset($node->getElementsByTagName('description')->item(0)->nodeValue)) {
 						$desc = $node->getElementsByTagName('description')->item(0)->nodeValue;
					} else {
					$desc = '';
					}
				    if (isset($node->getElementsByTagName('link')->item(0)->nodeValue)) {
 						$link = $node->getElementsByTagName('link')->item(0)->nodeValue;
					} else {
					$link = '';
					}
				    if (isset($node->getElementsByTagName('pubDate')->item(0)->nodeValue)) {
 						$date = $node->getElementsByTagName('pubDate')->item(0)->nodeValue;
					} else {
					$date = null;
					} 

  		        $item = array ( 
				    'title' => $title,
    				'desc' => $desc,
	    			'link' => $link,
		    		'date' => $date,
			    	);
			    array_push($feed, $item);
				}
		    } catch (Exception $e) {
		    }
		}

		for($x=0;$x<$feed_count;$x++) {
			$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
			$link = $feed[$x]['link'];
			echo '<p><strong><a href="'.$link.'" title="'.$title.'">'.$title.'</a></strong><br />';
			if ($feed[$x]['date'] != null) {			
			    $date = date('l F d, Y', strtotime($feed[$x]['date']));
			    echo '<small><em>Posted on '.$date.'</em></small></p>';
			}

			$description = $this->limitImageSizes($feed[$x]['desc'], $feed_image_max_width);
			if ($feed_desc_max_len > 0 && $feed_desc_max_len < strlen($description)) {
                $shortHtml = $this->truncateHtml($description, $feed_desc_max_len);
  			    echo '<p>'.$shortHtml.'    <a href="'.$link.'">Click Here To Read More.</a><br />&nbsp;</p>';
			} else {
			    echo '<p>'.$description.'</p>';
			}			
		}
		$themeobject->output( '</ul>' );
	}

	function limitImageSizes($htmlString, $maxWidth, $maxHeight = 0)
	{
  	    if ($maxHeight <= 0 && $maxWidth <= 0)
		    return $htmlString;

		$dom = new DomDocument();
		$dom->prevservWhiteSpace = false;
		libxml_use_internal_errors(true);
		$dom->loadHTML($htmlString);
		
		$imageList = $dom->getElementsByTagName('img');
		foreach ($imageList as $image) {
			$wDiff = 0;
	        $hDiff = 0;
            $width = $image->getAttribute('width');
            $height = $image->getAttribute('height');

			if ($width == null && $height == null) { // size not specified
			    $size = getimagesize($image->getAttribute('src'));  
				$width = $size[0];
				$height = $size[1];
			}
			
	        if ($maxWidth > 0 && $width > $maxWidth)
			    $wDiff = $width - $maxWidth;

	        if ($maxHeight > 0 && $height > $maxHeight)
			    $hDiff = $height - $maxHeight;

			$scale_factor = 0;
			if ($wDiff > 0) {
			    if ($wDiff >= $hDiff) 
			        $scale_factor = $maxWidth/$width;
				else
				    $scale_factor = $maxHeight/$height;			  
			} else {
			    if ($hDiff > 0)
				    $scale_factor = $maxHeight/$height;			  
			}
			
			if ($scale_factor > 0)
			{
			    $newWidth = floor($width * $scale_factor);
				$newHeight = floor($height * $scale_factor);
				$image->setAttribute("width", "$newWidth");
				$image->setAttribute("height", "$newHeight");
			}
		}

	    $fixedHtmlString = $dom->saveHTML();
		return $fixedHtmlString;
	}
	
/** FROM http://alanwhipple.com/2011/05/25/php-truncate-string-preserving-html-tags-words/
 * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
 *
 * @param string $text String to truncate.
 * @param integer $length Length of returned string, including ellipsis.
 * @param string $ending Ending to be appended to the trimmed string.
 * @param boolean $exact If false, $text will not be cut mid-word
 * @param boolean $considerHtml If true, HTML tags would be handled correctly
 *
 * @return string Trimmed string.
 */
function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
				// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);
					if ($pos !== false) {
					unset($open_tags[$pos]);
					}
				// if tag is an opening tag
				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, strtolower($tag_matchings[1]));
				}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
			if ($total_length+$content_length> $length) {
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if($total_length>= $length) {
				break;
			}
		}
	} else {
		if (strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = substr($text, 0, $length - strlen($ending));
		}
	}
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurance of a space...
		$spacepos = strrpos($truncate, ' ');
		if (isset($spacepos)) {
			// ...and cut the text in this position
			$truncate = substr($truncate, 0, $spacepos);
		}
	}
	// add the defined ending to the text
	$truncate .= $ending;
	if($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}	
}
