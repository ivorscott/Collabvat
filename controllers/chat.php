<?php
class ChatController extends Controller {

  public function __construct($action, $url) {
    parent::__construct($action, $url);
    $this->msg = new MessageModel();
    $this->room = new RoomModel();
    $this->user = new User();
  }

  /* ------------------ Viewable Pages ------------------ */

  public function index() {
    $this->view->output($this->model);
  }

  public function room() {
    // find room
    $hash = $this->url['id'];
    $this->room->find("hash",$hash);
    // // if room exists
    if ($this->room->id && $this->room->status != 'closed') {

        $this->model['room'] = $this->room;
        $this->model['user']  = $this->user->model;
        $this->model['activity']  = $this->msg->getActivity($this->room->id);
        // does the user have administrative access
        if ($this->user->model->id == $this->room->user_id) {
            $this->view->output($this->model);
            // if not is the user a guest ?
        } elseif ($this->room->guest_ids !== null) {
            $guests = explode(" ", $this->room->guest_ids);

            if ( in_array($this->user->model->id, $guests ) ) {
                $this->view->output($this->model);
            }
        } else {
            // create session to trigger toaster.js notification
            Redirect::to("/chat");
        }
    } else {
            Redirect::to("/chat");
    }
  }

  public function stats() {
      $this->view->output($this->model);
  }

  /* ------------------ Additional Methods ------------------ */

  public function setRoom() {
    // grab the json
    $request_body = file_get_contents("php://input");
    // decode json (automatically perserves obejcts)
    $data = json_decode($request_body);

    if (isset($data)) {

      // assign room object properties
      $this->room->user_id = $data->user_id;
      $this->room->image_ids = $data->image_ids;
      if($data->name != ''){
        $this->room->room_name = $data->name;
      }
      $this->room->guest_ids = $data->guest_ids;
      // Populate a room model using the last insertion
      $lastInsert = $this->room->store();
      $this->room->find("id", $lastInsert);
      $this->room->hash = md5($this->room->id);
      $this->room->store();

      // setup push notifications
      $pusher = new Pusher('aee53139485f1fc9c068', '66ab881678595db04fd3', '127855');
      $host = $this->user->model->fullname;
      $notification['message'] =
        $host . ' wants a critiqe<br>'
        . 'Join the conversation';
      $notification['link'] = '/chat/room/'. $this->room->hash;
      // trigger notification per guest channel
      $collection = array();
      $guest_ids = explode(" ", trim($data->guest_ids));
      foreach($guest_ids as $id) {
        $this->guest = new UserModel();
        $this->guest->find("id",$id);
        $channel = $this->guest->username . '_channel';
        $pusher->trigger($channel, 'new_notification', $notification);
      }
      // return hash to javascript
      echo $this->room->hash;
    }
  }

  public function setTime() {
    $request_body = file_get_contents("php://input");
    // decode json (automatically perserves obejcts)
    $data = json_decode($request_body);

    if (isset($data)) {
      $this->room->find('hash', $data->hash);
      $this->room->timestamp = $data->time;
      $this->room->store();
    }
  }

  public function closeRoom() {
      $request_body = file_get_contents("php://input");
      // decode json (automatically perserves obejcts)
      $data = json_decode($request_body);

      if (isset($data)) {
        $this->room->find('hash', $data->hash);
        $this->room->status = 'closed';
        $this->room->store();
        echo json_encode($this->room, JSON_PRETTY_PRINT);
      }
  }

  public static function saveMessageActivity($data) {
    if (isset($data)) {
      $msg = new MessageModel();
      $room = new RoomModel();
      $user = new UserModel();
      $room_id = (int) array_pop($data);
      $user->find('username',$data['actor']['displayName']);
      $json = json_encode($data);
      $msg->bind(array("text"=>$data["body"], "user_id"=>"{$user->id}", "room_id"=>$room_id, "activity"=>$json));
      $lastInsert = $msg->store();
      $room->find('id',$room_id);
      $room->message_ids = $room->message_ids . " " . $lastInsert;
      $room->store();
    }
  }

  public function saveImageCollection() {
    // set the date time
    $tz = $this->user->model->timezone;
    date_default_timezone_set($tz);
    // grab the json
    $request_body = file_get_contents("php://input");
    // decode json (automatically perserves obejcts)
    $data = json_decode($request_body);

    if (isset($data)) {
      $imageArray = array();

      for ( $i = 0; $i < sizeof($data); $i++) {
        // Add images
        $this->image = new ImageModel();
        $this->image->user_id = $data[$i]->user_id;
        $this->image->thumb_path = $data[$i]->thumb_path;
        $this->image->full_path = $data[$i]->full_path;
        $this->image->timestamp = date("Y-m-d H:i:s");
        $lastInsert = $this->image->store();
        // Populate an image model using the last insertion
        $this->image->find("id", $lastInsert);
        // push image model to array
        $imageArray[] = $this->image;
      }
    }
    echo json_encode($imageArray, JSON_PRETTY_PRINT);
  }

  public function saveCaptionCollection() {
    // grab the json
    $request_body = file_get_contents("php://input");
    // decode json (automatically perserves obejcts)
    $data = json_decode($request_body);

    if (isset($data)) {
      $imageArray = array();

      for ( $i = 0; $i < sizeof($data); $i++) {
        $this->image = new ImageModel();
        $this->image->find("id", $data[$i]->id);
        $this->image->caption = $data[$i]->caption;
        $this->image->store();
        $imageArray[] = $this->image;
      }
    }
    // return image collection to javascript
    echo json_encode($imageArray, JSON_PRETTY_PRINT);
  }
}

//// debuggin objects with utf8encoding
//$collection = array();
//$guest_ids = explode(" ", trim($data->guest_ids));
//foreach($guest_ids as $id) {
//  $this->guest = new UserModel();
//  $this->guest->find("id",$id);
//  $object = array();
//  foreach ($this->guest as $key => $value) {
//    if ($key == "password" || $key == "salt") continue;
//    $object[$key] = utf8_encode($value);
//  }
//  $collection[] = $object;
//}
//echo json_encode($collection, JSON_PRETTY_PRINT);
//}
