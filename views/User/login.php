<?php
$error = '';

if(Input::exists()) {
    $username = Input::get('username');
    $password = Input::get('password');
    $remember = (Input::get('remember') === 'on')? true : false;

    if(Token::check(Input::get('token'))) {

        $user = new User();
        $user->login($username, $password, $remember);

        if($user->exists()) {
          Redirect::to('/');
        }

        $error = '<p class="btn-danger">
          Sorry, that username and password was not recognised.</p>';
    }
}
?>
    <div id="login-pane">
      <div class="login-main">

        <h1>Collabvat</h1>

<?php if(Session::exists('home')) {
          echo '<p class="btn-success">', Session::flash('home'), '</p>';
      }

      if($error != '') {
         echo $error;
      }
?>

        <form id="login" action="" method="post" accept-charset="UTF-8">

          <p><input class="user_username" type="text" name="username" placeholder="Username" /></p>

          <p><input class="user_password" type="password" name="password" placeholder="Password" /></p>

          <p>
            <input class="user_remember" type="checkbox" name="remember"/>

            <label for="user_remember"> Remember me</label>
          </p>

          <input class="user_login btn btn-primary" type="submit" name="submit" value="Log In" />

          <a href="/register" class="user_register btn">Register Account</a>

          <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
        </form>

      </div>
    </div>

    <div id="intro-pane">
      <div id="video">
        <h1 class="bannerName">Collabvat</h1>
        <p class="slogan">A central hub for artists to critique art, share ideas, events and more.</p>
      </div>
    </div>
