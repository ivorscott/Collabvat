<?php

if(postExists()) {
  try {
    $upload = new Uploader();
    $upload->move();
    echo $upload->getUploaded();

  } catch (Exception $e) {
    echo $e->getMessage();
  }
}

function postExists() {
  return  isset($_FILES) && isset($_SERVER['HTTP_X_REQUESTED_WITH'])
  && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

class Uploader {
  protected $_uploaded = array();
  public $output = array();
  protected $_destination;
  protected $_path = "/models/images/";
  protected $_max = 25000000;
  protected $_messages = array();
  protected $_permitted = array('image/gif','image/jpg','image/jpeg','image/png');
  protected $_renamed = false;

  public function __construct($path = '') {

    if($path != '') {
      $this->_path = $path;
    }

    define('SCRIPT_BASE', realpath('../'));

    $this->_destination = SCRIPT_BASE . $this->_path;

    if(!is_dir($this->_destination) || !is_writable($this->_destination)) {
      throw new Exception("$path must be a valid, writable directory.");
    }

    $this->_uploaded = $_FILES;

  }

  public function move($overwrite = false) {
    $field = current($this->_uploaded);

    if(is_array($field['name'])) {
      foreach ($field['name'] as $number => $filename) {
        $this->_renamed = false;
        $this->processFile($filename, $field['error'][$number],
          $field['size'][$number], $field['type'][$number],
          $field['tmp_name'][$number], $overwrite);
      }
    } else {
      $this->processFile($field['name'], $field['error'], $field['size'],
        $field['type'], $field['tmp_name'], $overwrite);
    }
  }

  protected function processFile($filename, $error, $size, $type, $tmp_name, $overwrite) {
    $OK = $this->checkError($filename, $error);

    if ($OK) {
      $sizeOK = $this->checkSize($filename, $size);
      $typeOK = $this->checkType($filename, $type);

      if ($sizeOK && $typeOK) {

        $name = $this->checkName($filename, $overwrite);
        $image = $this->_destination . $name;

        $success = move_uploaded_file($tmp_name, $image);
        $this->createThumb($image);

        if ($success) {
          $message = $filename . ' uploaded successfully';
          if ($this->_renamed) {
            $message .= " and renamed $name";
          }
          $this->_messages[] = $message;
        } else {
          $this->_messages[] = 'Could not upload ' . $filename;
        }
      }
    }

  }

  public function setWidthHeight($width, $height, $maxwidth, $maxheight) {

    if ($width > $height) {
      if ($width > $maxwidth) {

        $difinwidth = $width / $maxwidth;
        $height = intval($height / $difinwidth);
        $width = $maxwidth;

        if ($height > $maxheight){

          $difinheight = $height / $maxheight;
          $width = intval($width / $difinheight);
          $height = $maxheight;
        }

      } else {

        if ($height > $maxheight){

          $difinheight = $height / $maxheight;
          $width = intval($width / $difinheight);
          $height = $maxheight;
        }
      }

    } else {
      if ($height > $maxheight) {

        $difinheight = $height / $maxheight;
        $width = intval($width / $difinheight);
        $height = $maxheight;

        if ($width > $maxwidth){

          $difinwidth = $width / $maxwidth;
          $height = intval($height / $difinwidth);
          $width = $maxwidth;
        }
      } else {

        if ($width > $maxwidth) {

          $difinwidth = $width / $maxwidth;
          $height = intval($height / $difinwidth);
          $width = $maxwidth;
        }
      }
    }

    $widthheightarr = array ("$width","$height");

    return $widthheightarr;
  }

  public function cleanFileName($file){
    $filename = explode('/images/', strtolower($file));
    $nohyphen = str_replace('-', '_', $filename[1]);
    $oneperiod = preg_replace('/[.](?![\w]{2,4}$)/', '', $nohyphen);
    $newname = $filename[0] .'/images/'. $oneperiod;
    return $newname;
  }

  public function createThumb ($filename, $constrainw=120, $constrainh=120){
    $img = $this->cleanFileName($filename);
    $oldsize = getimagesize ($img);
    $newsize = $this->setWidthHeight($oldsize[0], $oldsize[1], $constrainw, $constrainh);
    $parts = explode ("/", $img);
    $ext = explode('.', array_pop($parts));
    $src = '';

    if ($ext[1] == "gif"){

      $src = imagecreatefromgif ($img);

    } else if ($ext[1] == "jpg" || $ext[1] == "jpeg") {

      $src = imagecreatefromjpeg ($img);

    } else {

      $src = imagecreatefrompng ($img);
    }

    $dst = imagecreatetruecolor ($newsize[0],$newsize[1]);
    imagecopyresampled ($dst,$src,0,0,0,0,$newsize[0],$newsize[1],$oldsize[0],$oldsize[1]);
    $thumbname = implode('/',$parts).'/'.$ext[0] . "_th." . $ext[1];
    $parts = explode ("/", $thumbname);
    $this->output[] = array_pop($parts);

    if ($ext[1] == "gif"){
      imagegif ($dst,$thumbname);

    } else if ($ext[1] == "jpg" || $ext[1] == "jpeg") {
      imagejpeg ($dst,$thumbname);

    } else {
      imagepng ($dst,$thumbname);
    }
    imagedestroy ($dst);
    imagedestroy ($src);
    return $thumbname;
  }

  protected function checkError($filename, $error) {
    switch ($error) {
      case 0:
        return true;
      case 1:
      case 2:
        $this->_messages[] = "$filename exceeds maximum size: " . $this->getMaxSize();
        return true;
      case 3:
        $this->_messages[] = "Error uploading $filename. Please try again.";
        return false;
      case 4:
        $this->_messages[] = 'No file selected.';
        return false;
      default:
        $this->_messages[] = "System error uploading $filename. Contact webmaster.";
        return false;
    }
  }

  protected function checkSize($filename, $size) {
    if($size == 0) {
      return false;

    } elseif ($size > $this->_max) {
      $this->_messages[] = "$filename exceeds maximum size: " . $this->getMaxSize();
      return false;

    } else {
      return true;
    }
  }

  protected function checkType($filename, $type) {
    if(empty($type)) {
      return false;

    } elseif (!in_array($type, $this->_permitted)) {
      $this->_messages[] = "$filename is not a permitted type of file.";
      return false;

    } else {
      return true;
    }
  }

  protected function checkName($name, $overwrite) {

    $filename = strtolower($name);

    $one_period_only = preg_replace('/[.](?![\w]{2,4}$)/', '', $filename);

    $newname = str_replace(' ', '_', $one_period_only);

    if($newname != $name) {
      $this->_renamed = true;
      $this->refreshName($name,$newname);
    }
    if(!$overwrite) {

      $existing = scandir($this->_destination);

      if(in_array($newname, $existing)) {
        $dot = strrpos($newname, '.');

        if($dot) {
          $base = substr($newname, 0, $dot);
          $extension = substr($newname, $dot);
        } else {
          $base = $newname;
          $extension = '';
        }
        $i = 1;
        do {
          $newname = $base . '_' . $i++ . $extension;

        } while (in_array($newname, $existing));

        $this->_renamed = true;
        $this->refreshName($name,$newname);
      }
    }
    return $newname;
  }

  public function refreshName($oldname,$newname) {
    $imageLength = sizeof($this->_uploaded['media']['name']);

    for( $i = 0; $i < $imageLength; $i++) {

      if($this->_uploaded['media']['name'][$i] == $oldname) {
        $this->_uploaded['media']['name'][$i] = $newname;
      }
    }
  }

  public function addPermittedTypes($types) {
    $types = (array) $types;
    $this->isValidMime($types);
    $this->_permitted = array_merge($this->_permitted, $types);
  }

  public function setMaxSize($num) {
    if(!is_numeric($num)) {
      throw new Exception("Maximum size must be a number.");
    }
    $this->_max = (int) $num;
  }

  public function setPermittedTypes($types) {
    $types = (array) $types;
    $this->isValidMime($types);
    $this->_permitted = $types;
  }

  public function getMaxSize() {
    return number_format($this->_max/1024, 1) . 'kB';
  }

  public function getMessages() {
    return $this->_messages;
  }

  public function getUploaded() {

    $r = "<ul>";
    foreach($this->output as $img) {

      $r .= '<li><img class="thumb" src="'. $this->_path . $img . '" /></li>';

    }

    $r .= "</ul>";
    return $r;
  }

  protected function isValidMime($types) {
    $alsoValid = array('image/tiff',
      'application/pdf',
      'text/plain',
      'text/rtf');
    $valid = array_merge($this->_permitted, $alsoValid);

    foreach ($types as $type) {
      if(!in_array($type, $valid)) {
        throw new Exception("$type is not a permitted MIME type");
      }
    }
  }
}
