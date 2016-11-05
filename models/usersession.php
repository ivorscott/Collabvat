<?php
class UserSession extends Model {

public  $id,
        $user_id,
        $hash,
        $timestamp = null,
        $table = "users_session";
}