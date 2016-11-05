<?php
/**
* RESTful Web Service
*/
class ApiController extends Controller {

  public function __construct($action, $url) {

    parent::__construct($action, $url);

    $this->users = new UserModel();
    $this->images = new ImageModel();
    $this->rooms = new RoomModel();
  }

  public function search() {
    $request_body = file_get_contents("php://input");
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 1000');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-type: application/json');
    $req = htmlentities(json_decode($request_body));

    $db = DB::getInstance();
    $stmt = $db->prepare("SELECT * FROM users WHERE ((username LIKE ?) OR (fullname  LIKE ?))");
    $stmt->execute(array('%'.$req.'%','%'.$req.'%'));
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $arr = array();

    while($r = $stmt->fetch()){
      $obj = new stdClass();
      $obj->value = $r['fullname'];
      $obj->data = $r['username'];
      $obj->id = $r['id'];
      $arr[] = $obj;
    }

    echo json_encode($arr, JSON_PRETTY_PRINT);


//    $arr = array();
//    $obj = new stdClass();
//    $obj->value = "Scott Cummings";
//    $obj->data = "SC";
//    array_push($arr,$obj);
//    $obj = new stdClass();
//    $obj->value = "Julian Cummings";
//    $obj->data = "JC";
//    array_push($arr,$obj);

    //  echo json_encode($data, JSON_PRETTY_PRINT);

  }

  public function users() {
       $this->getJSON();
  }

  public function rooms() {
       $this->getJSON();
  }

  public function images() {
       $this->getJSON();
  }

  public function post() {

  }

  public function put() {

  }

  public function delete() {

  }

  public function getJSON() {

    // the model

    $model = $this->url['action'];

    // the query

    $query = $this->url['id'];

    // If an id exists and it's a number, find row

    if ( isset($query) and is_numeric($query)) {

        $object = array();
        $this->$model->find('id', $query);

        foreach ($this->$model as $key => $value) {

          if ($key == "password" || $key == "salt") continue;

          // Some characters go beyond ASCII, fix property values with encoding

          $object[$key] = utf8_encode($value);

        }

        // print the json
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        echo json_encode($object, JSON_PRETTY_PRINT);

    } else {

       /**
        * Return all rows under model name.
        */

        $collection = array();

        foreach ($this->$model->findAll() as $m) {

            $object = array();

            foreach ($m as $key => $value) {

                if ($key == "password" || $key == "salt") continue;

                // Some characters go beyond ASCII, fix property values with encoding

                $object[$key] = utf8_encode($value);
            }

            $collection[] = $object;
        }

        // print the json
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        echo json_encode($collection, JSON_PRETTY_PRINT);
    }
  }
}
