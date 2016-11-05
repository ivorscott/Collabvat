<?php
$errors = array();

if(Input::exists()) {

  if(Token::check(Input::get('token'))) {

    $validation = new Validate();

    $validation->check($_POST, array(
      'username' => array(
        'required' => true,
        'min' => 6,
        'max' => 20,
        'unique' => 'UserModel'),

      'email' => array(
        'required' => true,
        'unique' => 'UserModel'),

      'password' => array(
        'required' => true,
        'min' => 6),
      'password_again' => array(
        'required' => true,
        'matches' => 'password')
    ));

    if($validation->passed()) {

      $user = new User();

      $salt = Hash::salt(32);

      try {

        date_default_timezone_set(Input::get('timezone'));

        $user->create(array(
          'username'  => Input::get('username'),
          'email'  => Input::get('email'),
          'password'  => Hash::make(Input::get('password'), $salt),
          'salt'    => $salt,
          'privilege'   => 1,
          'timezone' =>  Input::get('timezone'),
          'joined' =>  date("Y-m-d H:i:s")
        ));

        Redirect::to('/');

      } catch(Exception $e){

        die($e->getMessage());
      }

    } else {

      foreach($validation->errors() as $error )
      {
        $error = "<p class='btn-danger'>".$error."</p>";
        $errors[] = $error;
      }
    }
  }
}
?>
    <script>

    $(function() {
      // Get user's timezone

      var tz = jstz.determine();
      timezone = tz.name();

      $('#tz_field').attr('value', timezone);
    });

    </script>

    <div id="login-pane">

      <div class="login-main">

        <h1>Collabvat</h1>

<?php
  foreach ($errors as $error ) {
    echo $error;
  }
?>
        <form id="login" action="" method="post" accept-charset="UTF-8">

          <p><input class="user_username" type="text" name="username" placeholder="Username" size="30" value="<?php echo Input::get('username'); ?>" /></p>

          <p><input class="user_email" type="email" name="email" placeholder="Email" size="30" value="<?php echo Input::get('email'); ?>" /></p>

          <p><input class="user_password" type="password" name="password" placeholder="Password" size="30" /></p>

          <p><input class="user_password" type="password" name="password_again" placeholder="Re-type password" size="30" /></p>

          <p><input class="user_register btn" type="submit" name="submit" value="Register Account" /></p>

          <input id="tz_field" type="hidden" name="timezone" value="">

          <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
          <br>
          <a class="back-btn" href="/"><i class="glyphicon glyphicon-arrow-left"></i> Go back</a>
        </form>

      </div>

    </div>

    <div id="intro-pane">
      <div id="video">
        <h1 class="bannerName">Collabvat</h1>
        <p class="slogan">A central hub for artists to critique art, share ideas, events and more.</p>
      </div>
    </div>
