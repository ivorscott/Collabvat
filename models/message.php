<?php
class MessageModel extends Model
{

  public $id = null,
    $text = null,
    $user_id = null,
    $room_id = null,
    $vote_ids = null,
    $activity = null,
    $timestamp = null,
    $table = "messages";

  public function getActivity($id) {

    $sql = "SELECT activity FROM {$this->table} WHERE room_id='$id'";

    $data = array();

    $db = DB::getInstance();

    $q = $db->query($sql);

    if ($q->rowCount()) {

      $q->setFetchMode(PDO::FETCH_ASSOC);

      while ($model = $q->fetch()) {

        $data[] = $model;
      }

      return $data;
    }
  }
}