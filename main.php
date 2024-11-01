<?php
/**
* @package SwiftNinjaProYoutubeEmbed
*/

if(!defined('ABSPATH')){
  echo '<meta http-equiv="refresh" content="0; url=/404">';
  die('404 Page Not Found');
}

if(!class_exists('SwiftNinjaProYoutubeEmbedMain')){

  class SwiftNinjaProYoutubeEmbedMain{

    public $pluginSettingsName;
    public $pluginShortcode;

    function start($pluginSettingsName, $pluginShortcode){
      $this->pluginSettingsName = $pluginSettingsName;
      $this->pluginShortcode = $pluginShortcode;

      add_filter('wp_head', array($this, 'prefetchDNS'));

      if($pluginShortcode){add_shortcode($pluginShortcode, array($this, 'add_plugin_shortcode'));}

      $altShortcode = $this->getSetting('AltShortcode', false);
      if($altShortcode && $altShortcode !== ''){
        add_shortcode($altShortcode, array($this, 'add_plugin_shortcode'));
      }

    }


    function add_plugin_shortcode($atts = ''){
      $value = shortcode_atts(array('id' => false, 'url' => false, 'videoid' => 'get_query', 'fallback' => false, 'fallbackurl' => false, 'fallbackid' => 'hide', 'width' => '100%', 'height' => 'ratio', 'title' => false, 'desc' => false, 'description' => false, 'fallbacktitle' => false, 'fallbackdesc' => false, 'fallbackdescription' => false,), $atts);
      $videoID = esc_html(strip_tags($value["id"]));
      if(!$videoID){$videoID = esc_html(strip_tags($value["url"]));}
      if(!$videoID){$videoID = esc_html(strip_tags($value["videoid"]));}
      $fallback = esc_html(strip_tags($value["fallback"]));
      if(!$fallback){$fallback = esc_html(strip_tags($value["fallbackurl"]));}
      if(!$fallback){$fallback = esc_html(strip_tags($value["fallbackid"]));}
      $title = esc_html(strip_tags($value["title"]));
      $desc = esc_html(strip_tags($value["desc"]));
      if(!$desc){$desc = esc_html(strip_tags($value["description"]));}
      $fallbackTitle = esc_html(strip_tags($value["fallbacktitle"]));
      $fallbackDesc = esc_html(strip_tags($value["fallbackdesc"]));
      if(!$fallbackDesc){$fallbackDesc = esc_html(strip_tags($value["fallbackdescription"]));}
      $vwidth = esc_html(strip_tags($value["width"]));
      $vheight = esc_html(strip_tags($value["height"]));

      $autoplay = false;
      if(isset($value["autoplay"])){$autoplay = true;}
      $mute = false;
      if(isset($value["mute"])){$mute = true;}

      if($videoID == 'get_query' && isset($_GET['v'])){
        $videoID = esc_html(strip_tags($_GET['v']));
      }else if($videoID == 'get_query'){$videoID = false;}
      if($fallback == 'get_query' && isset($_GET['v'])){
        $fallback = esc_html(strip_tags($_GET['v']));
      }else if($fallback == 'get_query'){$fallback = false;}

      if($videoID){$videoID = $this->getVideoUrl($videoID);}
      if(!$videoID && $fallback == 'hide'){return false;}
      else if(!$videoID && $fallback){$videoID = $this->getVideoUrl($fallback); $title = $fallbackTitle; $desc = $fallbackDesc;}
      if(!$videoID){return false;}

      if($autoplay){$videoID .= '&autoplay=1';}
      if($mute){$videoID .= '&mute=1';}

      $prefetch = '<link rel="prefetch" href="'.esc_url(strip_tags($videoID)).'">';
        $embed = '<iframe-border class="SwiftNinjaProYoutubeIframe"';
        if($vheight && $vheight == 'ratio'){$embed .= ' ratioheight'; $vheight = '60';}
      if($vwidth){$embed .= ' width="'.$vwidth.'"';}
      if($vheight){$embed .= ' height="'.$vheight.'"';}
      $embed .= '>';
      $embed .= '<iframe lazyloadurl="'.esc_url(strip_tags($videoID)).'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen';
      //if($vheight && $vheight == 'ratio'){$embed .= ' ratioheight'; $vheight = '60';}
      //if($vwidth){$embed .= ' width="'.$vwidth.'"';}
      //if($vheight){$embed .= ' height="'.$vheight.'"';}
      $embed .= '></iframe>';
      $embed .= '</iframe-border>';

      $videoPrefetch = '<link rel="prefetch" href="'.esc_url(strip_tags($videoID)).'">';

      $result = $prefetch;
      if($title){
        $result .= '<h3>'.esc_html__(strip_tags($title)).'</h3>';
      }
      $result .= $embed;
      if($desc){
        $result .= '<p>'.esc_html__(strip_tags($desc)).'</p>';
      }

      return $result;
    }


    function prefetchDNS($content){
      $result = '';
      $prefetchYoutubeDNS = $this->getSetting('PrefetchYoutubeDNS', true, true);
      $prefetchTwitchDNS = $this->getSetting('PrefetchTwitchDNS', true, true);
      if($prefetchYoutubeDNS){$result .= '<link rel="dns-prefetch" href="https://www.youtube.com">';}
      if($prefetchTwitchDNS){$result .= '<link rel="dns-prefetch" href="https://player.twitch.tv">';}
      $content .= $result;
    }


    function getVideoUrl($url){
      $vidDomain = false;
      $useUrl = esc_html($url);
      $type = false;
      if(strpos($useUrl, 'http://') !== false){$useUrl = str_replace('http://', '', $useUrl);}
      if(strpos($useUrl, 'https://') !== false){$useUrl = str_replace('https://', '', $useUrl);}
      if(strpos($useUrl, 'youtu.be') !== false){
        $vidDomain = 'youtube';
        if(strpos($useUrl, '/PL') !== false){
          $useUrl = str_replace('youtu.be/', '', $useUrl);
          $type = 'yt-playlist';
        }else if(strpos($useUrl, '/UC') !== false){
          $useUrl = str_replace('youtu.be/', '', $useUrl);
          $type = 'yt-channel';
        }else{
          $useUrl = str_replace('youtu.be/', '', $useUrl);
          $type = 'yt-video';
        }
      }else if(strpos($useUrl, 'youtube.com') !== false){
        $vidDomain = 'youtube';
        if(strpos($useUrl, '?v=') !== false && strpos($useUrl, '&') !== false){
          $useUrl = $this->get_string_between($useUrl, '?v=', '&');
          $type = 'yt-video';
        }else if(strpos($useUrl, '?v=') !== false){
          $useUrl = substr($useUrl, strpos($useUrl, '?v=') + 3);
          $type = 'yt-video';
        }else if(strpos($useUrl, '?list=') !== false && strpos($useUrl, '&') !== false){
          $useUrl = get_string_between($useUrl, '?list=', '&');
          $type = 'yt-playlist';
        }else if(strpos($useUrl, '?list=') !== false){
          $url = substr($useUrl, strpos($useUrl, '?list=') + 6);
          $type = 'yt-playlist';
        }else if(strpos($useUrl, 'channel/') !== false){
          $useUrl = substr($useUrl, strpos($useUrl, 'channel/') + 8);
          $useUrl = explode('/', $useUrl, 2)[0];
          $type = 'yt-channel';
        }
      }else if(strpos($useUrl, 'twitch.tv') !== false){
        $vidDomain = 'twitch';
        if(strpos($useUrl, 'videos/') !== false){
          $useUrl = substr($useUrl, strpos($useUrl, 'videos/') + 7);
          $type = 'twitch-video';
        }else if(strpos($useUrl, 'collections/') !== false && strpos($useUrl, '?') !== false){
          $useUrl = $this->get_string_between($useUrl, 'collections/', '?');
          $type = 'twitch-playlist';
        }else if(strpos($useUrl, 'collections/') !== false){
          $useUrl = substr($useUrl, strpos($useUrl, 'collections/') + 12);
          $type = 'twitch-playlist';
        }else if(strpos($useUrl, 'twitch.tv/') !== false){
          $useUrl = substr($useUrl, strpos($useUrl, 'twitch.tv/') + 10);
          $useUrl = strtok($useUrl, '/');
          $type = 'twitch-channel';
        }
      }

      $checkUrl = esc_html(strip_tags($useUrl));
      $checkUrl = ltrim($checkUrl, '/'); $checkUrl = rtrim($checkUrl, '/');
      if(!$type){$type = $this->check_video_type($checkUrl);}
      if(!$type){return false;}
      $embedUrl = false;
      if($type == 'yt-video' && ($vidDomain == 'youtube' || !$vidDomain)){
        $embedUrl = 'https://www.youtube.com/embed/'.$checkUrl.'?t=0';
      }else if($type == 'yt-playlist' && ($vidDomain == 'youtube' || !$vidDomain)){
        $embedUrl = 'https://www.youtube.com/embed?listType=playlist&list='.$checkUrl.'&t=0';
      }else if($type == 'yt-channel' && ($vidDomain == 'youtube' || !$vidDomain)){
        $embedUrl = 'https://www.youtube.com/embed?listType=playlist&list='.$checkUrl.'&t=0';
      }else if($type == 'yt-live' && ($vidDomain == 'youtube' || !$vidDomain)){
        $embedUrl = 'https://www.youtube.com/embed?live=1&channel='.$checkUrl;
      }else if(($type == 'yt-channel-UU' || $type == 'yt-live-UU') && substr($checkUrl, 0, 2) == 'UC' && ($vidDomain == 'youtube' || !$vidDomain)){
      	$checkChannelUrl = 'UU'.ltrim($checkUrl, 'UC');
				if($type == 'yt-channel-UU'){
					$embedUrl = 'https://www.youtube.com/embed?listType=playlist&list='.$checkChannelUrl.'&t=0';
				}else if($type == 'yt-live-UU'){
					$embedUrl = 'https://www.youtube.com/embed?live=1&channel='.$checkChannelUrl;
				}
    }else if($type == 'twitch-video' && ($vidDomain == 'twitch' || !$vidDomain)){
        $embedUrl = 'https://player.twitch.tv/?video='.$checkUrl;
      }else if($type == 'twitch-playlist' && ($vidDomain == 'twitch' || !$vidDomain)){
        $embedUrl = 'https://player.twitch.tv/?collection='.$checkUrl;
      }else if($type == 'twitch-channel' && ($vidDomain == 'twitch' || !$vidDomain)){
        $embedUrl = 'https://player.twitch.tv/?channel='.$checkUrl;
      }else{return false;}

      if($embedUrl){
        return $embedUrl;
      }
      return false;
    }

    function check_video_type($url){
      $checkUrl = esc_html(strip_tags($url));

      $disableYoutubeEmbed = $this->getSetting('DisableYoutubeEmbed', false, true);
      $disableTwitchEmbed = $this->getSetting('DisableTwitchEmbed', false, true);

      if(!$disableYoutubeEmbed){
        if($this->check_url_exists('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v='.$checkUrl.'&format=json')){
          return 'yt-video';
        }else if($this->check_url_exists('http://www.youtube.com/oembed?url=http://www.youtube.com/playlist?list='.$checkUrl.'&format=json')){
          return 'yt-playlist';
        }else if(substr($checkUrl, 0, 2) == 'UC'){
          $checkChannelUrl = 'UU'.ltrim($checkUrl, 'UC');
          if($this->check_url_exists('http://www.youtube.com/oembed?url=http://www.youtube.com/playlist?list='.$checkChannelUrl.'&format=json')){
            return 'yt-channel-UU';
          }
          return false;
        }
      }

      if(!$disableTwitchEmbed){
        if($this->check_url_exists('https://www.twitch.tv/videos/'.$checkUrl) || $this->check_url_exists('https://player.twitch.tv/?autoplay=false&video=v'.$checkUrl)){
          return 'twitch-video';
        }else if($this->check_url_exists('https://www.twitch.tv/collections/'.$checkUrl.'?filter=collections')){
          return 'twitch-playlist';
        }else if($this->check_url_exists('https://www.twitch.tv/'.$checkUrl)){
          return 'twitch-channel';
        }
      }

      if(!$disableYoutubeEmbed){
        if($this->check_url_exists('http://www.youtube.com/oembed?url=http://www.youtube.com/playlist?list='.$checkUrl.'&format=json')){
          return 'yt-channel';
        }
      }

      return false;
    }

    function check_url_exists($url){
			$url = str_replace('http://', 'https://', $url);

      $checkUrl = esc_url($url);
      $headers = get_headers($checkUrl);
      $header = strip_tags(esc_html($headers[0]));

      if(!isset($header) || !$header || $header === null || $header === ''){return false;}

      if(strpos($header, '200') !== false || strpos($header, '301') !== false || strpos($header, '302') !== false){return true;}
      if(strpos($header, '404') !== false || strpos($header, '403') !== false || strpos($header, 'Unauthorized') !== false || strpos($header, 'Not Found') !== false){return false;}

      $responseCode = (int) filter_var($str, FILTER_SANITIZE_NUMBER_INT);
      if($responseCode >= 200 && $responseCode < 400){
        return true;
      }else{
        return false;
      }

      //for($e = 200; $e <= 299; $e++){if(strpos($header, $e) !== false){return true;}}
      //for($e = 400; $e <= 599; $e++){if(strpos($header, $e) !== false){return false;}}
      //for($e = 300; $e <= 399; $e++){if(strpos($header, $e) !== false){return false;}}
      return true;
    }

    function trueText($text){
      if($text == 'true' || $text == 'TRUE' || $text == 'True' || $text == true || $text == 1){
        return true;
      }else{return false;}
    }

    function getSetting($name, $default = false, $trueText = false){
      $sName = esc_html(strip_tags('SwiftNinjaPro'.$this->pluginSettingsName.'_'.$name));
      $option = esc_html(strip_tags(get_option($sName)));
    if(!isset($option) || $option === null || $option === ''){return $default;}
      if($trueText){$option = $this->trueText($option);}
      return $option;
    }

    function get_string_between($string, $start, $end, $pos = 1){
      $cPos = 0;
      $ini = 0;
      $result = '';
      for($i = 0; $i < $pos; $i++){
        $ini = strpos($string, $start, $cPos);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        $result = substr($string, $ini, $len);
        $cPos = $ini + $len;
      }
      return $result;
    }

  }

  $swiftNinjaProYoutubeEmbedMain = new SwiftNinjaProYoutubeEmbedMain();

}
