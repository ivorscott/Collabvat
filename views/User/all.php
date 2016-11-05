<div class="container">
  <h1>Members</h1>
<?php
$users = $user->model->findAll();
$r = '';
foreach ($users as $u) {
  $r .= "<p><a href='/user/{$u['username']}'>" . $u['username'] . "</a></p>";
}
echo $r;
?>

</div>