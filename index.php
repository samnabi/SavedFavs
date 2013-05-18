<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Saved + Favs &middot; Because you don't need another read-it-later app.</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<h1>Saved + Favs</h1>

	<?php

		if(!empty($_GET['twitter']) || !empty($_GET['reddit_name']) || !empty($_GET['reddit_feed'])) {
			
			function linkify($text){
				// linkify URLs
				$text = preg_replace('/(https?:\/\/\S+)/','<a href="\1">\1</a>',$text);
				// linkify twitter users
				$text = preg_replace('/(^|\s)@(\w+)/','\1@<a href="http://twitter.com/\2">\2</a>',$text);
				// linkify tags
				$text = preg_replace('/(^|\s)#(\w+)/','\1#<a href="http://search.twitter.com/search?q=%23\2">\2</a>',$text);
				return $text;
			}

			// Twitter
			if(!empty($_GET['twitter'])) {

				require_once('codebird.php');
				$cb = Codebird::getInstance();
				$twitter_query = $cb->favorites_list(array('screen_name' => $_GET['twitter'], 'count' => '200'));

				foreach($twitter_query as $tweet){
					$item = '<li><p class="authorinfo clearfix"><img src="'.$tweet->user->profile_image_url.'" /><a class="username" href="https://twitter.com/'.$tweet->user->screen_name.'">@'.$tweet->user->screen_name.'</a></p><p class="tweet">'.linkify($tweet->text).'</p><a class="timestamp" href="https://twitter.com/'.$tweet->user->screen_name.'/status/'.$tweet->id_str.'">'.date('j M Y H:i',strtotime($tweet->created_at)).'</a></li>';
					$favs_array[strtotime($tweet->created_at)] = $item;
				}
			}

			// Reddit
			if(!empty($_GET['reddit_name']) && !empty($_GET['reddit_feed'])) {

		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_URL, 'http://www.reddit.com/user/'.$_GET['reddit_name'].'/saved.json?feed='.$_GET['reddit_feed'].'&user='.$_GET['reddit_name']); 
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		        $output = curl_exec($ch);
		        curl_getinfo($ch);
		        curl_close($ch); 

				$reddit_query = json_decode($output,true);

				foreach($reddit_query[data][children] as $post){
					$item = '<li><p class="authorinfo clearfix">';

					if($post[data][thumbnail] != 'self' && $post[data][thumbnail] != 'default'){
						$item .= '<img src="'.$post[data][thumbnail].'" />';
					}

					$item .= '<a class="username" href="http://reddit.com/r/'.$post[data][subreddit].'">/r/'.$post[data][subreddit].'</a></p><p class="tweet"><a href="'.$post[data][url].'">'.$post[data][title].'</a></p><a class="timestamp" href="http://reddit.com'.$post[data][permalink].'">'.date('j M Y H:i',$post[data][created]).'</a></li>';

					$favs_array[$post[data][created]] = $item;
				}
			}

			?>

			<ul class="accounts">
				<li><a href="index.php">Back to Home</a></li>
				<?php
					if(!empty($_GET['twitter'])) echo '<li>Twitter: '.$_GET['twitter'].'</li>';
					if(!empty($_GET['reddit_name'])) echo '<li>Reddit: '.$_GET['reddit_name'].'</li>';
				?>
			</ul>

			<ul class="fav_tweets">
				<?php
					// sort and print the feed
					krsort($favs_array);
					foreach($favs_array as $post){
						echo $post."\n";
					}
				?>
			</ul>

			<script src="http://code.jquery.com/jquery.min.js"></script>
			<script src="jquery.isotope.min.js"></script>
			<script>
				$('.fav_tweets').isotope({
					itemSelector: '.fav_tweets li',
			  		layoutMode : 'masonry'
				});
			</script>
			<?php
		}
		else {
			?>
			<form method="GET" action="index.php">
				<p class="intro">No need for a separate read-it-later app. Get your favourited Tweets and saved Reddit posts all in one place.</p>
				<p>
					<label for="twitter">Twitter Username: </label>
					<input name="twitter" type="text" value="" />
				</p>
				<p>
					<label for="reddit">Reddit Saved Posts URL (<a target="_blank" href="reddit-help.gif">Where do I find this?</a>): </label>
					<span class="reddit_url">http://reddit.com/user/<input name="reddit_name" type="text" value="" />/saved.json?feed=<input name="reddit_feed" type="text" value="" />&user=[your_username]</span>
				</p>
				<p>
					<input type="submit" value="Submit" />
				</p>
				<p class="intro"><small>Bookmark the following page to avoid filling out this form again.</small></p>
			</form>
			<?php
		}
	?>

</body>
</html>