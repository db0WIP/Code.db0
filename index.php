<?php;
// ************************************************************************** //
// Project: Code.db0                                                          //
// Description: Simple web-based code viewer in PHP                           //
// Author: db0 (db0company@gmail.com, http://db0.fr/)                         //
// Latest Version is on GitHub: https://github.com/db0company/Code.db0        //
// ************************************************************************** //

include_once "conf.php";
include_once "markdown.php";
include_once "geshi.php";

// ************************************************************************** //
// Tools                                                                      //
// ************************************************************************** //

function protect($string) {
  return htmlspecialchars(stripslashes($string));
}

function	file_not_found($filename) {
  echo '      <div class="alert alert-error">',
    protect($filename), ' not found.</div>', "\n";
}

function	multi_pattern_matching($patterns, $str) {
  if (!is_array($patterns))
    return false;
  foreach ($patterns as $pattern)
    if (fnmatch($pattern, $str))
      return true;
  return false;
}

function	crop_keep_word($str, $len) {
  if (strlen($str) < $len || $len < 1)
    return $str;
  elseif (@preg_match("/(.{1,$len})\s./ms", $str, $match))
    return $match[1];
  else
    return substr($str, 0, $len);
}

function	file_extansion($path) {
  return pathinfo($path, PATHINFO_EXTENSION);
}

// ************************************************************************** //
// Geshi Tools                                                                //
// ************************************************************************** //

// Array of extansion -> langages
$known_languages = array('php', 'ml', 'c', 'sh');

// ************************************************************************** //
// Include external Bootstrap files                                           //
// ************************************************************************** //

function        css_include() {
  global $bootstrap_path;
  if (!($handler = @opendir($bootstrap_path.'/css/')))
    return false;
  while ($file = @readdir($handler))
    if ($file[0] != '.'
	&& pathinfo($file, PATHINFO_EXTENSION) == 'css')
      $css .= '    <link href="'. $bootstrap_path. '/css/'. $file.
	'" rel="stylesheet" />'."\n";
  return $css;
}

function	js_include() {
  global $bootstrap_path, $jquery_path;
  if (!file_exists($jquery_path)
      || !($handler = @opendir($bootstrap_path.'/js/')))
    return false;
  while ($file = @readdir($handler))
    if ($file[0] != '.'
	&& pathinfo($file, PATHINFO_EXTENSION) == 'js')
      $js .= '      <script src="'. $bootstrap_path. '/js/'. $file.'"></script>'. "\n";
  $js .= '      <script src="'. $jquery_path.'"></script>'. "\n";
  $js .= '      <script type="text/javascript"> 
        function show(node_name) {
             $("#"+node_name).slideToggle("fast");
          }
      </script>'."\n";
  return $js;
}

// ************************************************************************** //
// Header                                                                     //
// ************************************************************************** //

function	http_header($project) {
  global $title, $description, $author, $author_contact, $author_website;
  global $favicon;
  $return = true;
  echo '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="utf-8" />
    <title>', $title, (empty($project) ? '' : ' :: '.$project), '</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="', $description, '" />
    <meta name="author" content="', $author, ' ', $author_contact,
    ' ', $author_website, '" />
';
  if (!($css = css_include()))
    $return = false;
  else
    echo $css;
  echo '    <style type="text/css">
      body {
        padding-top: 20px;
        padding-bottom: 40px;
      }
      .hero-unit img {
        float: right;
      }
      .hero-unit p {
        margin-top: 20px;
      }
      .hide {
        display: none;
      }
      .onclick {
        cursor: pointer;
        font-size: 0.8em;
        text-align: right;
      }
    </style>
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->', "\n";
  if (!empty($favicon))
    echo '    <link rel="shortcut icon" href="', $favicon,'" />', "\n";
  echo '    <script>window["_GOOG_TRANS_EXT_VER"] = "1";</script>
  </head>', "\n";
  return $return;
}

function	page_header() {
  global $logo, $title, $description, $author, $author_website;
  echo '      <div class="hero-unit">', "\n";
  if (!empty($logo))
    echo '        <img src="',$logo,'" alt="',$title,' - ',$description,'" />', "\n";
  echo '	<h1>', $title, '</h1>
	<p>', $description, '</p>
        <p><a class="btn btn-primary btn-large" href="', $author_website,
    '" target="_blank">
	    About ', $author, ' »</a></p>
      </div>', "\n";
}

// ************************************************************************** //
// Footer                                                                     //
// ************************************************************************** //

function	page_footer() {
  global $author, $author_contact, $author_website;
  echo '      <hr />

      <footer>
	  <p>© Copyright ', @date('Y'), ' -
	  By <b>', $author, '</b> - 
	  Contact : <a href="mailto:', $author_contact, '">
	      ', $author_contact, '</a> - 
	  Website: <a href="', $author_website, '">
	      ', $author_website, '</a>
	  </p>
      </footer>';
}

// ************************************************************************** //
// Get informations about projects                                            //
// ************************************************************************** //

function	is_project($project) {
  global $projects_path;
  $path = $projects_path.'/'.$project;
  return @file_exists($path) && @is_dir($path);
}

function	get_project_description($project, $crop = false) {
  global $projects_path, $max_descr;
  $project_path = $projects_path.'/'.$project;
  $files_for_descriptions = array('.description', 'README', 'README.md', 'README.markdown');
  $max_descr = (isset($max_descr) ? $max_descr : 255);

  foreach ($files_for_descriptions as $filename) {
    $filename = $project_path.'/'.$filename;
    if (@file_exists($filename)) {
      $full_description = @file_get_contents($filename);
      if ($crop)
	$description = crop_keep_word($full_description, $max_descr).($crop ? '...' : '');
      $description = '<div id="'. $project. '_description">'."\n".@Markdown($description)
	."\n".'</div>'."\n";
      if ($crop && strlen($full_description) > $max_descr)
      	$description .= '<div class="onclick" onClick="show(\''.$project.'_description\');show(\''
      	  .$project.'_details\'); return(false);">Details...</div>'."\n"
      	  .'<div class="hide" id="'.$project.'_details">'.@Markdown($full_description).'</div>';
      return $description;
    }
  }
  return '      <div class="alert alert-warn">No description for '.$project.'.</div>'. "\n";
}

function	get_projects_list($path) {
  global $ignore;
  if (!($handle = @opendir($path))) {
    file_not_found($path);
    return false;
  }
  $projects_list = array();
  while ($filename = @readdir($handle)) {
    if (!multi_pattern_matching($ignore, $filename)
	&& @is_dir($path.'/'.$filename))
      $projects_list[] = $filename;
  }
  return $projects_list;
}

// ************************************************************************** //
// Tools to manage projects                                                   //
// ************************************************************************** //

function	sort_projects_list($projects_list) {
  global $order_by;
  if ($order_by == 'name')
    @sort($projects_list);
  elseif ($order_by == 'random')
    @shuffle($projects_list);
  elseif ($order_by == 'date');
  elseif ($order_by == 'langage');
}

function	show_projects_list($projects_list) {
  $i = 0;
  echo '      <div class="row">', "\n";
  foreach ($projects_list as $project) {
    if ($i && !($i % 3))
      echo '      </div>'."\n".'      <hr />',"\n",'      <div class="row">', "\n";
    echo '        <div class="span4">', "\n";
    echo '          <h2>'.$project.'</h2>', "\n";
    echo '          ', get_project_description($project, true), "\n";
    echo '          <p><a class="btn" href="?p=',$project,'">View project »</a></p>', "\n";
    echo '        </div>', "\n";
    ++$i;
  }
  echo '      </div>';
  echo "\n";
}

// ************************************************************************** //
// Pages                                                                      //
// ************************************************************************** //

function	home_page() {
  global $projects_path;
  if (!($projects_list = get_projects_list($projects_path)))
    return ;
  sort_projects_list($projects_list);
  show_projects_list($projects_list);
}

function	show_file_list_item($project, $dir, $filename, $type)
{
  echo '<li><a href="?p=', $project.'&f=', $dir, '/', $filename, '">';
  echo '<i class="icon-', $type, '"></i> ', $filename;
  echo '</a></li>', "\n";
}

function	show_file_list($project, $dir) {
  global $projects_path, $ignore;
  $path = $projects_path.'/'.$dir;
  if (!($handler = @opendir($path.'/'.$project)))
    return ;
  echo '<div class="well">
<ul class="nav nav-list">
  <li class="nav-header">Browse files</li>'."\n";
  $files = array();
  $dirs = array();
  while ($file = @readdir($handler)) {
    if (!multi_pattern_matching($ignore, $file)) {
      if (@is_dir($path.'/'.$project.'/'.$file))
	$dirs[] = $file;
      else
	$files[] = $file;
    }
  }
  @sort($files);
  @sort($dirs);
  foreach ($dirs as $file)
    show_file_list_item($project, $dir, $file, 'folder-open');
  foreach ($files as $file)
    show_file_list_item($project, $dir, $file, 'file');
  echo '</ul></div>';
}

function	view_file($project, $file) {
  global $projects_path;
  $path = $projects_path.'/'.$project.'/'.$file;
  if (!file_exists($path))
    return file_not_found($file);
  $file_content = file_get_contents($path);
  $geshi = new GeSHi($file_content, file_extansion($path));
  echo $geshi->parse_code();
}

function	project_page($project) {
  global $projects_path;
  $file = protect($_GET['f']);
  echo '<div class="row">';
  echo '<div class="span9">';
  if (empty($file))
    echo get_project_description($project);
  else
    view_file($project, $file);
  echo '</div>';
  echo '<div class="span3">';
  show_file_list($project, '.');
  echo '</div></div>';
}

// ************************************************************************** //
// Manage pages                                                               //
// ************************************************************************** //

function	page_content($project) {
  if (empty($project))
    home_page();
  else
    project_page($project);
}

function	main() {
  $project = protect($_GET['p']);
  if (!is_project($project))
    $project = '';
  $err_markdown = !function_exists('Markdown');
  $err_css = !http_header($project);
  if (!($js = js_include()))
    $err_js = true;
  echo '  <body>', "\n\n", '    <div class="container">', "\n";
  if ($err_css || $err_js)
    file_not_found('Bootstrap or JQuery');
  elseif ($err_markdown)
    file_not_found('Markdown');
  else {
      page_header();
      page_content($project);
      page_footer();
    }
  echo '    </div>', "\n\n";
  echo $js;
  echo '  </body>', "\n", '</html>';
}

main();
