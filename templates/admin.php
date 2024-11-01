<?php

if(!defined('ABSPATH') || !current_user_can('manage_options')){
  echo '<meta http-equiv="refresh" content="0; url=/404">';
  die('404 Page Not Found');
}

global $SwiftNinjaProSettings_PluginName;
$SwiftNinjaProSettings_PluginName = 'YoutubeEmbed';
$SwiftNinjaProSettings_PluginDisplayName = 'Youtube Embed';
$SwiftNinjaProSettings_PluginPermalinkName = 'youtube-embed';

$SwiftNinjaProSettingAdminOnly = SwiftNinjaProSettingsTrueText(SwiftNinjaPro_settings_GetOption_only('AdminOnly'));

if(!current_user_can('administrator') && $SwiftNinjaProSettingAdminOnly){
  die('Only Administrators Are Allowed To Access These Settings!');
}

?>

<style>
.swiftninjaro-settings-pre{
    border: solid 3px #2b333d;
    border-radius: 10px;
    font-size: 14px;
    color: #3c3d3c;
    margin: 2%;
    padding: 10px;
    background: #eaeaea;
    display: block;
    font-family: monospace;
    white-space: pre-wrap;
    width: 85%;
}

.swiftninjapro-settings-button {
  all: initial;
  all: unset;
  border: solid 2px #3a3a3a;
  color: #f7f7f7;
  background: #2877c1;
  border-radius: 10px;
  font-size: 30px;
  padding: 10px;
}

.swiftninjapro-settings-button:hover {
  border: solid 2px #0f0f0f;
  color: #e8e8e8;
  background: #2269aa;
}
</style>

<datalist id="SwiftNinjaProSettingsColorPicker">
  <option value="#FF0000">
  <option value="#FFC0CB">
  <option value="#FFA500">
  <option value="#FFD700">
  <option value="#FFFF00">
  <option value="#800080">
  <option value="#00FF00">
  <option value="#008000">
  <option value="#00FFFF">
  <option value="#ADD8E6">
  <option value="#0000FF">
  <option value="#00008B">
  <option value="#8B4513">
  <option value="#FFFFFF">
  <option value="#D3D3D3">
  <option value="#A9A9A9">
  <option value="#808080">
  <option value="#000000">
</datalist>

<?php

echo "<h1>SwiftNinjaPro $SwiftNinjaProSettings_PluginDisplayName</h1>";

if(!isset($_GET['UpdateOptions']) && isset($_GET['settings'])){
  if(esc_html($_GET['settings']) === 'session-error'){
    echo '<h2>Error: Failed to save settings! Session Expired!</h2>';
  }else if(esc_html($_GET['settings']) === 'saved'){
    echo '<h2>Successfully Saved Settings!</h2>';
  }
}

if(isset($_GET['UpdateOptions']) && (!isset($_POST['SwiftNinjaProSettingsToken']) || (esc_html($_POST['SwiftNinjaProSettingsToken']) !== esc_html($_COOKIE['SwiftNinjaProSettingsToken']) && esc_html($_POST['SwiftNinjaProSettingsToken']) !== esc_html($_REQUEST['SwiftNinjaProSettingsToken'])))){
  echo '<script>window.location.replace("'.esc_url(get_admin_url()).'admin.php?page=swiftninjapro-'.$SwiftNinjaProSettings_PluginPermalinkName.'&settings=session-error");</script>';
  exit('<h2>Error: Failed to save settings! Session Expired!</h2>');
}
$SwiftNinjaProSettingsToken = esc_html(wp_generate_password(64));
$SwiftNinjaProSettingsDomain = preg_replace('/^https?:\/\//', '', esc_url(get_admin_url()));
$SwiftNinjaProSettingsDomain = explode('/', $SwiftNinjaProSettingsDomain, 2);
setcookie('SwiftNinjaProSettingsToken', $SwiftNinjaProSettingsToken, 0, '/'.$SwiftNinjaProSettingsDomain[1], $SwiftNinjaProSettingsDomain[0]);


//get and update options
$SwiftNinjaProSettingsEnabled = SwiftNinjaPro_settings_GetOption('Enabled', 'manage_options');
$SwiftNinjaProSettingsAdminOnly = SwiftNinjaPro_settings_GetOption('AdminOnly', 'administrator');

$SwiftNinjaProSettingsPrefetchYoutubeDNS = SwiftNinjaPro_settings_GetOption('PrefetchYoutubeDNS', 'manage_options');
$SwiftNinjaProSettingsPrefetchTwitchDNS = SwiftNinjaPro_settings_GetOption('PrefetchTwitchDNS', 'manage_options');

$SwiftNinjaProSettingsDisableYoutubeEmbed = SwiftNinjaPro_settings_GetOption('DisableYoutubeEmbed', 'manage_options');
$SwiftNinjaProSettingsDisableTwitchEmbed = SwiftNinjaPro_settings_GetOption('DisableTwitchEmbed', 'manage_options');

$SwiftNinjaProSettingsAltShortcode = SwiftNinjaPro_settings_GetOption('AltShortcode', 'manage_options');


if(isset($_GET['UpdateOptions'])){
  echo '<script>window.location.replace("'.esc_url(get_admin_url()).'admin.php?page=swiftninjapro-'.$SwiftNinjaProSettings_PluginPermalinkName.'&settings=saved");</script>';
  exit('<h2>Successfully Saved Settings!</h2>');
}

function SwiftNinjaPro_settings_GetOption($name, $requiredPermToUpdate){
  global $SwiftNinjaProSettings_PluginName;
  $pluginName = $SwiftNinjaProSettings_PluginName;
  $sName = SwiftNinjaPro_settings_SetOption($name, $pluginName);
  $option = get_option($sName);
  if(isset($option) && ($option || $option === false || $option === '')){
    $option = esc_html($option);
  }else{$option = null;}
  if(isset($_GET['UpdateOptions'])){
    $post = esc_html($_POST[$sName]);
    if(current_user_can($requiredPermToUpdate)){update_option($sName, $post);}
    return $post;
  }else{return $option;}
}

function SwiftNinjaPro_settings_GetOption_only($name){
  global $SwiftNinjaProSettings_PluginName;
  $pluginName = $SwiftNinjaProSettings_PluginName;
  $sName = SwiftNinjaPro_settings_SetOption($name, $pluginName);
  $option = get_option($sName);
  if(isset($option) && $option){
    $option = esc_html($option);
  }else{$option = null;}
  return $option;
}

function SwiftNinjaPro_settings_SetOption($name, $pluginName){
  return esc_html('SwiftNinjaPro'.$pluginName.'_'.$name);
}

function SwiftNinjaProSettingsTrueText($text){
  if($text === 'true' || $text === 'TRUE' || $text === 'True' || $text === true || $text === 1 || $text === 'on'){
    return true;
  }else{return false;}
}


//option display functions
function SwiftNinjaProSettingAddCheckBox($setting, $option, $text, $default=false){
  global $SwiftNinjaProSettings_PluginName;
  $pluginName = $SwiftNinjaProSettings_PluginName;
  $sName = SwiftNinjaPro_settings_SetOption($option, $pluginName);
  $set;
  if($setting !== null){
    $set = SwiftNinjaProSettingsTrueText($setting);
  }else{$set = $default;}
  if($set){
    echo '<input type="checkbox" name="'.$sName.'" checked="true"><strong>'.$text.'</strong></input>';
  }else{
    echo '<input type="checkbox" name="'.$sName.'"><strong>'.$text.'</strong></input>';
  }
}

function SwiftNinjaProSettingAddList($setting, $option, $text, $default=false){
  global $SwiftNinjaProSettings_PluginName;
  $pluginName = $SwiftNinjaProSettings_PluginName;
  $sName = SwiftNinjaPro_settings_SetOption($option, $pluginName);
  $set;
  if(isset($setting) && $setting && $setting !== ''){
    $set = $setting;
  }else if($default){
    $set = $default;
  }else{$set = '';}
  $result = '<textarea class="swiftninjapro-settings-textarea" name="'.$sName.'" rows="10" cols="20" placeholder="'.$text.'">';
  $result = $result.$set;
  $result = $result.'</textarea>';
  echo $result;
}

function SwiftNinjaProSettingAddInput($setting, $option, $text, $default, $inputSize, $placeholder = false, $type = "text"){
  global $SwiftNinjaProSettings_PluginName;
  $pluginName = $SwiftNinjaProSettings_PluginName;
  $sName = SwiftNinjaPro_settings_SetOption($option, $pluginName);
  $setValue;
  if($setting){
    $setValue = $setting;
  }else{$setValue = $default;}

  $setPlaceholder = $text;
  if($placeholder){
    $setPlaceholder = $placeholder;
  }

  $result = '<strong>'.$text.' </strong>';
  $result .= '<input type="'.$type.'" name="'.$sName.'" placeholder="'.$setPlaceholder.'" value="'.$setValue.'" style="border-radius: 10px; width: '.$inputSize.';"/>';

  echo $result;
}

function SwiftNinjaProSettingAddColorPicker($setting, $option, $text, $default=false){
  global $SwiftNinjaProSettings_PluginName;
  $pluginName = $SwiftNinjaProSettings_PluginName;
  $sName = SwiftNinjaPro_settings_SetOption($option, $pluginName);
  $setColor;
  if($setting){
    $setColor = $setting;
  }else{$setColor = $default;}
  $result = '<strong>'.$text.' </strong>';
  $result .= '<input type="color" id="swiftninjapro-colorpickerselect-'.$option.'" name="'.$sName.'" value="'.$setColor.'" style="padding: 1px; width: 75px; height: 25px;" list="SwiftNinjaProSettingsColorPicker" onchange="swiftninjaproSettingSetColorPicker'.$option.'(this.value)">';
  $result .= '</input>';
  $result .= ' <input id="swiftninjapro-colorpickerinput-'.$option.'" maxlength="7" placeholder="#hex" value="'.$setColor.'" style="border-radius: 10px; width: 100px;" onpaste="swiftninjaproSettingSetColorPicker'.$option.'(this.value)" oninput="swiftninjaproSettingSetColorPicker'.$option.'(this.value)"></input>';
  $result .= '<script>function swiftninjaproSettingSetColorPicker'.$option.'(value){var select = document.getElementById("swiftninjapro-colorpickerselect-'.$option.'"); var input = document.getElementById("swiftninjapro-colorpickerinput-'.$option.'"); select.value = value; input.value = value;}</script>';

  echo $result;
}

function SwiftNinjaProSettingAddSelect($setting, $option, $text, $default, $optionList, $optionNameList){
  global $SwiftNinjaProSettings_PluginName;
  $pluginName = $SwiftNinjaProSettings_PluginName;
  $sName = SwiftNinjaPro_settings_SetOption($option, $pluginName);
  $setValue;
  if($setting){
    $setValue = $setting;
  }else{$setValue = $default;}
  $result = '<strong>'.$text.' </strong>';
  $result .= '<select id="'.$option.'" name="'.$sName.'" style="border-radius: 10px;">';
  for($i = 0; $i < count($optionList); $i++){
    $name = $optionList[$i];
    if($optionNameList[$i] != ""){
      $name = $optionNameList[$i];
    }
    if($optionList[$i] == $setValue){
      $result .= '<option value="'.$optionList[$i].'" selected>'.$name.'</option>';
    }else{$result .= '<option value="'.$optionList[$i].'">'.$name.'</option>';}
  }
  $result .= '</select>';
  echo $result;
}


//settings form
echo '<form action="'.esc_url(get_admin_url()).'admin.php?page=swiftninjapro-'.$SwiftNinjaProSettings_PluginPermalinkName.'&UpdateOptions" autocomplete="off" method="POST" enctype="multipart/form-data">';
echo '<input type="hidden" name="SwiftNinjaProSettingsToken" value="'.$SwiftNinjaProSettingsToken.'">';

SwiftNinjaProSettingAddCheckBox($SwiftNinjaProSettingsEnabled, 'Enabled', 'Plugin Enabled', true);
echo '<br>';
SwiftNinjaProSettingAddCheckBox($SwiftNinjaProSettingsAdminOnly, 'AdminOnly', 'Restrict Settings To Administrator');
echo '<br><br>';

SwiftNinjaProSettingAddCheckBox($SwiftNinjaProSettingsPrefetchYoutubeDNS, 'PrefetchYoutubeDNS', 'Prefetch Youtube DNS', true);
echo '<br>';
SwiftNinjaProSettingAddCheckBox($SwiftNinjaProSettingsPrefetchTwitchDNS, 'PrefetchTwitchDNS', 'Prefetch Twitch DNS', true);
echo '<br><br>';

SwiftNinjaProSettingAddCheckBox($SwiftNinjaProSettingsDisableYoutubeEmbed, 'DisableYoutubeEmbed', 'Disable Youtube Embed');
echo '<br>';
SwiftNinjaProSettingAddCheckBox($SwiftNinjaProSettingsDisableTwitchEmbed, 'DisableTwitchEmbed', 'Disable Twitch Embed');
echo '<br><br>';

SwiftNinjaProSettingAddInput($SwiftNinjaProSettingsAltShortcode, 'AltShortcode', 'Alternate Shortcode', false, '200px', 'example: yt');
echo '<br><br>';

echo '<input type="submit" value="Save" class="swiftninjapro-settings-button"><br><br>';

echo '<pre class="swiftninjaro-settings-pre">';

echo '<h2>Shortcode Examples:</h2>';
echo '<h4>[ytembed url="YouTube Or Twitch Url"]</h4>';
echo '<h4>[ytembed id="VideoOrPlaylistId"]</h4>';
echo '<br>';

echo '<h3>Optional:</h3>';
echo '<br>';

echo '<h2>Shortcode parameters:</h2>';
echo '<h4>id - id of (video | playlist | channel | twitch video | twitch channel | twitch collection)</h4>';
echo '<h5> - set "id" to "get_query" (or leave blank) to get the query var "?v=" from the url</h5>';
echo '<h4>fallback - leave blank to hide or set it as you would id</h4>';
echo '<h5> - fallback will show if the video "id" is unavalible or private</h5>';
echo '<h4>title - a title you can optionally display above the video embed (this will be hidden if the video is unavalible)</h4>';
echo '<h4>desc - a description you can optionally display below the video embed (this will be hidden if the video is unavalible)</h4>';
echo '<h4>fallbacktitle - the "title" for the fallback video</h4>';
echo '<h4>fallbackdesc - the "desc" for the fallback video</h4>';
echo '<h4>width - optionally set video embed width (default is 100%)</h4>';
echo '<h4>height - optionally set video embed height (default will calculate a 16/9 ratio based on the width)</h4>';
echo '<h4>autoplay - if added as a parameter, the youtube video will autoplay</h4>';
echo '<h4>mute - if added as a parameter, the youtube video will be muted (sometimes youtube will refuse autoplay on monetised videos unless mute is set)</h4>';

echo '</pre>';

echo '</form>';

?>
