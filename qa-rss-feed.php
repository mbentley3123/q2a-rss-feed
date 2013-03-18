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
			$description = $feed[$x]['desc'];
			echo '<p><strong><a href="'.$link.'" title="'.$title.'">'.$title.'</a></strong><br />';
			if ($feed[$x]['date'] != null) {			
			    $date = date('l F d, Y', strtotime($feed[$x]['date']));
			    echo '<small><em>Posted on '.$date.'</em></small></p>';
			}
			echo '<p>'.$description.'</p>';
		}
		$themeobject->output( '</ul>' );
	}
}
