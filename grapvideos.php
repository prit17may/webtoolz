<?php

// Grapvideo single video youtube
function grab_video_youtube_single($url) {

    $url = parse_url($url);
    $vid = parse_str($url['query'], $output);
    $video_id = $output['v'];
    $data['video_type'] = 'youtube';
    $data['video_id'] = $video_id;
    //$xml                = simplexml_load_file("https://gdata.youtube.com/feeds/api/videos/{$video_id}?v=2");
    $xml = simplexml_load_file("http://gdata.youtube.com/feeds/api/videos?q={$video_id}&start-index=1&max-results=1&v=2");
    foreach ($xml->entry as $entry) {
        // get nodes in media: namespace
        $media = $entry->children('http://search.yahoo.com/mrss/');
        // get video player URL
        $attrs = $media->group->player->attributes();
        $watch = $attrs['url'];
        // get video thumbnail
        $data['thumb_1'] = $media->group->thumbnail[0]->attributes(); // Thumbnail 1
        $data['thumb_2'] = $media->group->thumbnail[1]->attributes(); // Thumbnail 2
        $data['thumb_3'] = $media->group->thumbnail[2]->attributes(); // Thumbnail 3
        $data['thumb_large'] = $media->group->thumbnail[3]->attributes(); // Large thumbnail
        $data['tags'] = $media->group->keywords; // Video Tags
        $data['cat'] = $media->group->category; // Video category
        $attrs = $media->group->thumbnail[0]->attributes();
        $thumbnail = $attrs['url'];
        // get <yt:duration> node for video length
        $yt = $media->children('http://gdata.youtube.com/schemas/2007');
        $attrs = $yt->duration->attributes();
        $data['duration'] = $attrs['seconds'];
        // get <yt:stats> node for viewer statistics
        $yt = $entry->children('http://gdata.youtube.com/schemas/2007');
        $attrs = $yt->statistics->attributes();
        $data['views'] = $viewCount = $attrs['viewCount'];
        $data['title'] = $entry->title;
        $data['info'] = $entry->content;
        // get <gd:rating> node for video ratings
        $gd = $entry->children('http://schemas.google.com/g/2005');
        if ($gd->rating) {
            $attrs = $gd->rating->attributes();
            $data['rating'] = $attrs['average'];
        } else {
            $data['rating'] = 0;
        }
    } // End foreach

    $video = array();
    $video[0]['index'] = 1;
    $video[0]['video_id'] = $data['video_id'];
    $video[0]['title'] = (string) $data['title'];
    $duration = number_format($data['duration'] / 60, 2, ':', '');
    $video[0]['duration'] = $duration;
    $video[0]['video_source'] = 'Youtube';
    return $video;
}

// Grapper from user or playlist YT
function grab_video_youtube_user_or_playlist($youtube_method, $user_or_playlist_id, $start) {
    if ($youtube_method == 'user') {
        $url = file_get_contents("https://gdata.youtube.com/feeds/api/users/" . $user_or_playlist_id . "/uploads/?v=2&alt=json&max-results=50&start-index=" . $start);
    } else if ($youtube_method == 'playlist') {
        $url = file_get_contents("https://gdata.youtube.com/feeds/api/playlists/" . $user_or_playlist_id . "?v=2&alt=json&max-results=50&start-index=" . $start);
    } else {
        $url = '';
    }
    $decode = json_decode($url, TRUE); // TRUE for in array format

    $data = array();
    $i = 0;
    foreach ($decode['feed']['entry'] as $entry) {
        $data[$i]['index'] = $start;
        $data[$i]['video_id'] = $entry['media$group']['yt$videoid']['$t'];
        $data[$i]['title'] = $entry['title']['$t'];
        $duration = number_format($entry['media$group']['media$content'][0]['duration'] / 60, 2, ':', '');
        $data[$i]['duration'] = $duration;
        $data[$i]['video_source'] = 'Youtube';
        $i++;
        $start++;
    }



    return $data;
}

// Check Pagination grabber
function is_more_50_video($user_or_playlist_id, $start) {
    $url = file_get_contents("https://gdata.youtube.com/feeds/api/users/" . $user_or_playlist_id . "/uploads/?v=2&alt=json&max-results=50&start-index=" . $start);
    $decode = json_decode($url, TRUE); // TRUE for in array format
    return $decode;
}

// Grabber from Vimeo
function grab_video_vimeo($url) {
    $video_id = explode('vimeo.com/', $url);
    $video_id = $video_id[1];
    $data['video_type'] = 'vimeo';
    $data['video_id'] = $video_id;
    $xml = simplexml_load_file("http://vimeo.com/api/v2/video/$video_id.xml");
    foreach ($xml->video as $video) {
        $data['id'] = $video->id;
        $data['title'] = $video->title;
        $data['info'] = $video->description;
        $data['url'] = $video->url;
        $data['upload_date'] = $video->upload_date;
        $data['mobile_url'] = $video->mobile_url;
        $data['thumb_small'] = $video->thumbnail_small;
        $data['thumb_medium'] = $video->thumbnail_medium;
        $data['thumb_large'] = $video->thumbnail_large;
        $data['user_name'] = $video->user_name;
        $data['urer_url'] = $video->urer_url;
        $data['user_thumb_small'] = $video->user_portrait_small;
        $data['user_thumb_medium'] = $video->user_portrait_medium;
        $data['user_thumb_large'] = $video->user_portrait_large;
        $data['user_thumb_huge'] = $video->user_portrait_huge;
        $data['likes'] = $video->stats_number_of_likes;
        $data['views'] = $video->stats_number_of_plays;
        $data['comments'] = $video->stats_number_of_comments;
        $data['duration'] = $video->duration;
        $data['width'] = $video->width;
        $data['height'] = $video->height;
        $data['tags'] = $video->tags;
    } // End foreach


    $video = array();
    $video[0]['index'] = 1;
    $video[0]['video_id'] = $data['video_id'];
    $video[0]['title'] = (string) $data['title'];
    $duration = number_format($data['duration'] / 60, 2, ':', '');
    $video[0]['duration'] = $duration;

    return $video;
}

// Grabber from Dailymotion
function grab_video_daiymotion($url) {


    $url_full = $url . '?fields=duration,id%2Ctitle';
    $url_api = str_replace('http://www.dailymotion.com', 'https://api.dailymotion.com', $url_full);
    $dailymotion = file_get_contents($url_api);

    $data = json_decode($dailymotion, true);

    $video = array();
    $video[0]['index'] = 1;
    $video[0]['video_id'] = $data['id'];
    $video[0]['title'] = $data['title'];
    $duration = number_format($data['duration'] / 60, 2, ':', '');
    $video[0]['duration'] = $duration;

    return $video;
}

/* * *************************  FUNC FOR VINE******************** */

// Grabber from Vine
function grab_video_vine($url) {
    $vId = explode('/', $url);
    $vTitle = get_vine_title($url);
    $video = array();
    $video[0]['index'] = 1;
    $video[0]['video_id'] = $vId[4];
    $video[0]['title'] = (string) $vTitle;
    $video[0]['duration'] = 'Unknown';
    $video[0]['video_source'] = 'Vine';

    return $video;
}

// Get vine stream
function get_vine_stream($vId) {
    $vine = file_get_contents("http://vine.co/v/{$vId}");
    preg_match('/property="twitter:player:stream" content="(.*?)"/', $vine, $matches);

    return ($matches[1]) ? $matches[1] : false;
}

// Get vine stream
function get_vine_title($url) {
    $data = file_get_contents($url);
    preg_match('/property="twitter:title" content="(.*?)"/', $data, $matches);
    return $matches[1];
}

/* * ************************ END FOR VINE ******************* */

//Grabber from facebook
function grab_video_facebook($fb_user_id, $access_token_offline_access) {

    require_once(getcwd() . "/core/Facebook/facebook.php");

    $facebook = new Facebook(array(
                'appId' => fb_application_id,
                'secret' => fb_secret_key,
                'cookie' => true,
            ));


  //$access_token_offline_access = 'CAADMmgmTET8BAEc3DVi7NBMx0x2tn1jZAX7KGZB4UxEqEZCo0ExgMPLQZA1NOzGha7le2ZALZAekZADQKKXaZAxjRrCA57AjytL11DLolRJQB8ayDVm96yhWwccLgUAaK8pWMHlPwuMo9zfAoHH3PzTyKQ4U5ZASbDoVywzZBcCqMv35bPMNaQuRUH'; //works
  $response = $facebook->api(
				'/' . $fb_user_id .  '/videos/uploaded/?fields=id,name,embed_html,created_time', 'GET', array(
				'access_token' => $access_token_offline_access,
            )
    );


    
		$data = array();
		$i = 0;
		$start = 1;


		foreach ($response['data'] as $value) {
			$data[$i]['index'] = 1;
			$data[$i]['video_id'] = $value['id'];

			if (isset($value['name'])) {
				$data[$i]['title'] = $value['name'];
			}else{
				$data[$i]['title'] = 'Not title';
			}
		   
			$data[$i]['duration'] = 'Unknown';
			$data[$i]['video_source'] = 'Facebook';
			$i++;
			$start++;
		}

    return $data;
	
}

// Grabber video by oembed services
function grab_video_by_oembed($url) {

    $library_link_arr = array(
        //  '1'  => 'youtube.com',
        '2' => 'vimeo.com',
        '3' => 'dailymotion.com',
        '4' => 'viddler.com',
        '5' => 'qik.com',
        '6' => 'revision3.com',
        '7' => 'hulu.com',
        '8' => 'jest.com',
        '9' => 'funnyordie.com',
        '10' => 'videojug.com',
        '11' => 'videos.sapo.pt',
        '12' => 'justin.tv',
        '13' => 'blip.tv',
        '14' => 'ustream.tv',
    );

    $is_link = '';

    foreach ($library_link_arr as $link) {
        if (strpos($url, $link)) {
            $is_link = $link;
        }
    }

    $_url = '';
    switch ($is_link) {
//        case 'youtube.com':
//            $_url     = 'http://www.youtube.com/oembed?url=' . $url . '&format=json';
//            $data = get_data_from_oembed($_url);
//            $video_id = get_id_youtube($data['html']);
//            break;
        case 'vimeo.com':
            $_url = 'http://vimeo.com/api/oembed.json?url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_vimeo($data['html']);
            break;
        case 'dailymotion.com':
            $_url = 'http://www.dailymotion.com/services/oembed?format=json&url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_dailymotion($data['html']);
            break;
        case 'viddler.com':
            $_url = 'http://www.viddler.com/oembed/?format=json&url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_viddler($data['html']);
            break;
        case 'qik.com':
            $_url = 'http://qik.com/api/oembed.json?url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_qik($data['html']);
            break;
        case 'revision3.com':
            $_url = 'http://revision3.com/api/oembed/?url=' . $url . '/&format=json';
            $data = get_data_from_oembed($_url);
            $video_id = get_id_revision3($data['html']);
            break;
        case 'hulu.com':
            $_url = 'http://www.hulu.com/api/oembed.json?url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_hulu($data['html']);
            break;
        case 'jest.com':
            $_url = 'http://www.jest.com/oembed.json?url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_jest($data['html']);
            break;
        case 'funnyordie.com':
            $_url = 'http://www.funnyordie.com/oembed.json?url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_funnyordie($data['html']);
            break;
        case 'videojug.com':
            $_url = 'http://www.videojug.com/oembed.json?url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_videojug($data['html']);
            break;
        case 'videos.sapo.pt':
            $_url = 'http://videos.sapo.pt/oembed?url=' . $url . '&format=json';
            $data = get_data_from_oembed($_url);
            $video_id = get_id_sapo($data['html']);
            break;
        case 'justin.tv':
            $_url = 'http://api.justin.tv/api/embed/from_url.json?url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_justin($data['html']);
            break;
        case 'blip.tv':
            $_url = 'http://blip.tv/oembed/?url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_blip($data['html']);
            break;
        case 'ustream.tv':
            $_url = 'http://www.ustream.tv/oembed?url=' . $url;
            $data = get_data_from_oembed($_url);
            $video_id = get_id_ustream($data['html']);
            $provider = 'Ustream';
            break;
        default:
            break;
    }


    if (isset($data)) {
        $video = array();
        $video[0]['index'] = 1;
        $video[0]['title'] = $data['title'];
        $video[0]['video_id'] = $video_id;
        if ($data['duration'] != '') {
            $video[0]['duration'] = number_format($data['duration'] / 60, 2, ':', '');
        } else {
            $video[0]['duration'] = 'Unknown';
        }

        if ($data['provider_name'] != '') {
            $video[0]['video_source'] = $data['provider_name'];
        } else {
            if (isset($provider)) {
                $video[0]['video_source'] = $provider;
            }
        }
        return $video;
    }

    return '';
}

// Get data grabber by oembed

function get_data_from_oembed($_url) {

    $data0 = file_get_contents($_url);
    $data = json_decode($data0, true);

    return $data;
}

// Get data youtube
function get_id_youtube($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("?", $src);
    $embed_id = explode('/', $embed_arr[0]);
    return $embed_id[4];
}

// Get data vimeo
function get_id_vimeo($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("/", $src);

    return $embed_arr[4];
}

// Get data dailymotion
function get_id_dailymotion($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("/", $src);
    return $embed_arr[5];
}

// Get data viddler
function get_id_viddler($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("/", $src);
    return $embed_arr[4];
}

// Get data qik
function get_id_qik($videoEmbed) {
    $src = get_src_from_embed($videoEmbed);
    $embed_arr = explode("/", $src);
    $video_id = explode('?', $embed_arr[4]);

    return $video_id[1];
}

// Get data revision3
function get_id_revision3($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("?", $src);

    $embed_id = explode('&', $embed_arr[1]);
    $video_id = explode('=', $embed_id[0]);
    return $video_id[1];
}

function get_id_hulu($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("/", $src);
    $embed_id = explode('?', $embed_arr[3]);
    $video_id = explode('=', $embed_id[1]);
    return $video_id[1];
}

// Get data jest
function get_id_jest($videoEmbed) {

    $src = get_src_from_embed($videoEmbed);

    $embed_arr = explode("&", $src);
    $embed_id = explode('=', $embed_arr[1]);
    return $embed_id[1];
}

// Get data funnyordie
function get_id_funnyordie($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("/", $src);

    return $embed_arr[4];
}

// Get data videojug
function get_id_videojug($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("/", $src);

    return $embed_arr[4];
}

// Get data sapo
function get_id_sapo($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("/", $src);

    return $embed_arr[6];
}

// Get data justin
function get_id_justin($videoEmbed) {
    $src = get_src_from_param4($videoEmbed);
    $embed_arr = explode("&", $src);

    $embed_id = explode('=', $embed_arr[4]);
    return $embed_id[1];
}

// Get data justin
function get_id_blip($videoEmbed) {
    $src = get_src_from_iframe($videoEmbed);
    $embed_arr = explode("/", $src);
    $embed_id = explode('?', $embed_arr[4]);
    return $embed_id[0];
}

// Get data justin
function get_id_ustream($videoEmbed) {
    $src = get_src_from_param0($videoEmbed);
    $embed_arr = explode("&", $src);

    $embed_id = explode('=', $embed_arr[2]);
    return $embed_id[1];
}

// GET SRC EMBED  BY IFRAME
function get_src_from_iframe($videoEmbed) {
    $doc = new DOMDocument();
    $doc->loadHTML($videoEmbed);

    $src = $doc->getElementsByTagName('iframe')->item(0)->getAttribute('src');
    return $src;
}

//GET SRC EMBED FROM OBJECT
function get_src_from_embed($videoEmbed) {
    //$videoEmbed_ = serialize($videoEmbed);
    $doc = new DOMDocument();
    $doc->loadHTML($videoEmbed);

    $src = $doc->getElementsByTagName('embed')->item(0)->getAttribute('src');
    return $src;
}

//GET SRC EMBED FROM OBJECT
function get_src_from_param4($videoEmbed) {
    //$videoEmbed_ = serialize($videoEmbed);
    $doc = new DOMDocument();
    $doc->loadHTML($videoEmbed);

    $src = $doc->getElementsByTagName('param')->item(4)->getAttribute('value');
    return $src;
}

//GET SRC EMBED FROM OBJECT
function get_src_from_param0($videoEmbed) {
    // $videoEmbed_ = serialize($videoEmbed);
    $doc = new DOMDocument();
    $doc->loadHTML($videoEmbed);

    $src = $doc->getElementsByTagName('param')->item(0)->getAttribute('value');
    return $src;
}

?>
