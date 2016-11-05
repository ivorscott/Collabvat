<?php
abstract class Model {

protected   $id,
            $table;
  
  public function bind( array $data ) {
  
    foreach ( $data as $key => $value ) {
    	
      if ($key == "id") continue;
   
    	$this->$key = $value;
    }
  }
  
  public function find($key, $value) {

    $db = DB::getInstance();

    $sql = "SELECT * FROM {$this->table} WHERE {$key} = '{$value}'";

    $q = $db->query($sql);
    
    $count = $q->rowCount();
    
    if($count) {

      $q->setFetchMode(PDO::FETCH_ASSOC);

      $results = $q->fetch();
      
      $this->id = $results['id'];

      $this->bind($results);
    }
    
    $q->closeCursor();
    $q = null;

    return $count;
  }

  public function findAll() {

    $db = DB::getInstance();

    $data = array();

  	$q = $db->query("SELECT * FROM $this->table");

    if($q->rowCount()) {

      $q->setFetchMode(PDO::FETCH_ASSOC);
  
      while ($model = $q->fetch()) {

        $data[] = $model;
      }
    }

		return $data;	
  }
  
  public function store() {

    $db = DB::getInstance();

   	$sql = $this->buildQuery('store');

   	$stmt = $db->prepare($sql);
   	
    $classVars = get_class_vars(get_class($this));
  
 	  foreach ($classVars as $key=>$value) {

	    if ($key == "id" || $key == "table" || $this->$key == null) continue;
	   
			$fields[":{$key}"] = $this->$key;
   	}

   	$stmt->execute($fields);
   	$stmt->closeCursor();
   	$stmt = null;
    
    return $db->lastInsertId();
  }
  
  public function drop() {
    $db = DB::getInstance();

    $sql = $this->buildQuery('drop');
    $db->query($sql);
    $db = null;
  }
  
  protected function buildQuery( $task ) {

    $sql = "";

    if ( $task == "store" ) {

      if ( $this->id == null ) { 

      $keys = "";
      $values = "";
      $placeholder = "";

      $class_vars = get_class_vars(get_class($this));

      $sql .= "INSERT INTO {$this->table} ";

      foreach ($class_vars as $key=>$value) {

        if( $key == "id" || $key == "table" || $this->$key == null) continue;

        $keys .= "{$key},";
        $placeholder.= ":{$key},";
      } 
      $sql .= "(".substr($keys, 0, -1).") Values (". substr($placeholder, 0, -1) .")";
      $sql = str_replace("'NOW()'","NOW()",$sql); 
      } else {

        $class_vars = get_class_vars(get_class($this));
        $sql .= "UPDATE {$this->table} SET ";

        foreach ($class_vars as $key=>$value) {

          if ($key == "id" || $key == "table" || $this->$key == null) continue;

         @ $keys .= "{$key} = :{$key}, ";
        }
        $sql .= substr($keys, 0, -2) .  " WHERE id = {$this->id} LIMIT 1";
      }

    } else {
      $sql = "DELETE FROM {$this->table} WHERE id = {$this->id}";

    }
    return $sql;
  }
}