<?php
/*
  $Id: upload.php,v 1.1.1.1 2004/03/04 23:39:49 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class upload {
    var $file, $filename, $destination, $permissions, $extensions, $tmp_filename, $message_location;

    function upload($file = '', $destination = '', $permissions = '777', $extensions = '') {

      $this->set_file($file);
      $this->set_destination($destination);
      $this->set_permissions($permissions);
      $this->set_extensions($extensions);

      $this->set_output_messages('direct');

      if (tep_not_null($this->file) && tep_not_null($this->destination)) {
        $this->set_output_messages('session');

        if ( ($this->parse() == true) && ($this->save() == true) ) {
          return true;
        } else {
// self destruct
         // $this = null;
         unset($this);
// JACK COMMENTED THIS OUT TO MAKE THE ADMIN WORK FOR PHP 5.0.3 ,
// Tom added new unset for php5

          return false;
        }
      }
    }

    function parse() {
      global $messageStack;
//print('file : '.$this->file.'<br>');
      if (isset($_FILES[$this->file])) {
//print("line 46 parse :: <br>");
        $file = array('name' => $_FILES[$this->file]['name'],
                      'type' => $_FILES[$this->file]['type'],
                      'size' => $_FILES[$this->file]['size'],
                      'tmp_name' => $_FILES[$this->file]['tmp_name']);
      } elseif (isset($GLOBALS['POST'][$this->file])) {
        global $_POST;
//print("line 53 parse :: <br>");
        $file = array('name' => $_POST[$this->file]['name'],
                      'type' => $_POST[$this->file]['type'],
                      'size' => $_POST[$this->file]['size'],
                      'tmp_name' => $_POST[$this->file]['tmp_name']);
      } else {
//print("line 59 parse :: <br>");
        $file = array('name' => (isset($GLOBALS[$this->file . '_name']) ? $GLOBALS[$this->file . '_name'] : ''),
                      'type' => (isset($GLOBALS[$this->file . '_type']) ? $GLOBALS[$this->file . '_type'] : ''),
                      'size' => (isset($GLOBALS[$this->file . '_size']) ? $GLOBALS[$this->file . '_size'] : ''),
                      'tmp_name' => (isset($GLOBALS[$this->file]) ? $GLOBALS[$this->file] : ''));
      }
//print("line 65 parse :: <br>");
//print("<xmp>");
//print_r($file);
//print_r(is_uploaded_file($file['tmp_name']));
//print("</xmp>");


      if ( tep_not_null($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name']) ) {
//print('line 73 <br>');
        if (sizeof($this->extensions) > 0) {
//print('line 75 <br>');

//print("hhhhhhhhhhhhh :: ".strtolower(substr($file['name'], //strrpos($file['name'], '.')+1))."<br>");

//print("<xmp>");
//print_r($this->extensions);
//print("</xmp>");


          if (!in_array(strtolower(substr($file['name'], strrpos($file['name'], '.')+1)), $this->extensions)) {
//print('line 77 <br>');
            if ($this->message_location == 'direct') {
//print('line 79 <br>');
//print('line 79'.ERROR_FILETYPE_NOT_ALLOWED.' <br>');
              $messageStack->add('search', ERROR_FILETYPE_NOT_ALLOWED, 'error');
            } else {
//print('line 82 <br>');
              $messageStack->add_session('search', ERROR_FILETYPE_NOT_ALLOWED, 'error');
            }

            return false;
          }
        }
//print('line 86 <br>');
        $this->set_file($file);
        $this->set_filename($file['name']);
        $this->set_tmp_filename($file['tmp_name']);

//print('check_destination : '.$this->check_destination().'<br>');

        return $this->check_destination();
      } else {
//print('line 92 <br>');
      if (!empty($file['tmp_name'])){
        if ($this->message_location == 'direct') {
          $messageStack->add('search', WARNING_NO_FILE_UPLOADED, 'warning');
        } else {
          $messageStack->add_session('search', WARNING_NO_FILE_UPLOADED, 'warning');
        }
      }
        return false;
     }
  }

    function save() {
      global $messageStack;

      if (substr($this->destination, -1) != '/') $this->destination .= '/';

      if (move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
        chmod($this->destination . $this->filename, $this->permissions);

        if ($this->message_location == 'direct') {
          $messageStack->add('search', SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
        } else {
          $messageStack->add_session('search', SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
        }

        return true;
      } else {
        if ($this->message_location == 'direct') {
          $messageStack->add('search', ERROR_FILE_NOT_SAVED, 'error');
        } else {
          $messageStack->add_session('search', ERROR_FILE_NOT_SAVED, 'error');
        }

        return false;
      }
    }

    function set_file($file) {
      $this->file = $file;
    }

    function set_destination($destination) {
      $this->destination = $destination;
    }

    function set_permissions($permissions) {
      $this->permissions = octdec($permissions);
    }

    function set_filename($filename) {
      $this->filename = $filename;
    }

    function set_tmp_filename($filename) {
      $this->tmp_filename = $filename;
    }

    function set_extensions($extensions) {
      if (tep_not_null($extensions)) {
        if (is_array($extensions)) {
          $this->extensions = $extensions;
        } else {
          $this->extensions = array($extensions);
        }
      } else {
        $this->extensions = array();
      }
    }

    function check_destination() {
      global $messageStack;

      if (!is_writeable($this->destination)) {
        if (is_dir($this->destination)) {
          if ($this->message_location == 'direct') {
            $messageStack->add('search', sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
          } else {
            $messageStack->add_session('search', sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
          }
        } else {
          if ($this->message_location == 'direct') {
            $messageStack->add('search', sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
          } else {
            $messageStack->add_session('search', sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
          }
        }

        return false;
      } else {
        return true;
      }
    }

    function set_output_messages($location) {
      switch ($location) {
        case 'session':
          $this->message_location = 'session';
          break;
        case 'direct':
        default:
          $this->message_location = 'direct';
          break;
      }
    }
  }
?>
