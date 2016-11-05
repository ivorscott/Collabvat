<?php
$db = DB::getInstance();

$user = new User();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Collabvat | <?=$model['title']?> </title>
  <link rel="stylesheet" href="/assets/dist/css/cv-brand-bootstrap.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
  <!--[if lt IE 9]>
  <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  <script src="http://js.pusher.com/3.0/pusher.min.js"></script>

  <script src="/assets/dist/js/all.js"></script>

  <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
  <script>

    var pusher = new Pusher('aee53139485f1fc9c068');

    // Enable pusher logging - don't include this in production
    Pusher.log = function(message) {
      if (window.console && window.console.log) {
        window.console.log(message);
      }
    };

    var channel = "<?php echo $user->model->username . '_channel';?>";
    var notificationsChannel = pusher.subscribe(channel);
    notificationsChannel.bind('new_notification', function(notification){
      var message = notification.message;
      var link = notification.link;

      toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "showDuration": "300",
        "hideDuration": "300",
        "timeOut": "12000",
        "extendedTimeOut": "2000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
      };
      toastr.info(message,"<h3>You've been Invited</h3>");

      $('.toast-info').on('click',function(){
        window.location.href=link;
      });
    });
  </script>
</head>
<body>
<style>
  .autocomplete-suggestions { border: 1px solid #999; background: #FFF; cursor: default; overflow: auto; -webkit-box-shadow: 1px 4px 3px rgba(50, 50, 50, 0.64); -moz-box-shadow: 1px 4px 3px rgba(50, 50, 50, 0.64); box-shadow: 1px 4px 3px rgba(50, 50, 50, 0.64); }
  .autocomplete-suggestion { white-space: nowrap; overflow: hidden; }
  .autocomplete-selected { background: #F0F0F0; }
  .autocomplete-suggestions strong { font-weight: bold; color: #000; }
  .navbar-form .input-group {
    position: relative;
    background: #fff;
    border-radius: 5px;
  }
  #autocomplete-ajax {
    font-size: 16px;
    z-index: 3;
    background: transparent;
  }
  .autocomplete-suggestion span {
    color: #bbb;
  }
  #autocomplete-ajax-x {
    color: #CCC;
    position: absolute;
    background: transparent;
    z-index: 2;
    left: 12px;
    top: 4px;
    border: none;
  }
  .autocomplete-group strong { font-weight: bold; font-size: 16px; color: #000; display: block; border-bottom: 1px solid #000; }
</style>
<div id="app" class="container">
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Test: Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">
            <img alt="Brand" src="/assets/images/brand-image.gif" width="20" height="20">
            <span>Collabvat</span>
          </a>
        </div><!--/.navbar-header -->
        <?php
        if($user->isLoggedIn()) { ?>

          <div id="navbar" class="navbar-collapse collapse no-gutter">
            <div class="col-lg-2 col-md-2 col-sm-2">
              <ul class="nav navbar-nav navbar-left">
                <li class="navbar-critique <?=($model['title']=='chat' || $model['title']=='room')?'active':'';?>"><a href="/"><span class="glyphicon glyphicon-comment"></span></a></li>
                <li class="navbar-events <?=($model['title']=='events')?'active':'';?>"><a href="/events"><span class="glyphicon glyphicon-calendar"></span></a></li>
              </ul>
            </div>
            <ul class="nav navbar-nav navbar-right">
              <li class="navbar-requests dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <span class="glyphicon glyphicon-user"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                <?php
                // print requests
                $sql = "SELECT `from` FROM frnd_req WHERE `to`='{$user->model->id}'";
                $q = $db->query($sql);

                if($q->rowCount()) {
                  $q->setFetchMode(PDO::FETCH_ASSOC);
                  while ($data = $q->fetch()) {
                    $results[] = $data;
                  }
                }

                $r = '';
                $r.="<li class='dropdown-header'><b style='color:#840FA6;'>{$q->rowCount()}</b> Friend Requests</li>";
                $r.='<li role="separator" class="divider"></li>';
                $q->closeCursor();
                $q = null;
                $whatUser = new UserModel();

                foreach ($results as $request) {
                  $whatUser->find('id',$request['from']);
                  $r .= "<li><a href='/user/{$whatUser->username}'><span class='glyphicon glyphicon-plus'></span> {$whatUser->username}</a></li>";
                }
                echo $r;
                ?>
                </ul>
              </li>
              <li class="navbar-alerts dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <span class="glyphicon glyphicon-bell"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                  <li class="dropdown-header">Notifications</li>
                  <li role="separator" class="divider"></li>
                  <li><a href="#">Some notification</a></li>
                  <li><a href="#">Something else here</a></li>
                </ul>
              </li>
              <li class="navbar-user<?=($model['title']=='user' || $model['title']=='friends')?'active':'';?>"><a href="/user/<?=$user->model->username;?>"> <?=$user->model->username;?></a></li>
              <li class="navbar-settings dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <span class="glyphicon glyphicon-cog"></span><span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                  <li class="dropdown-header">Account Settings</li>
                  <li role="separator" class="divider"></li>
                  <li><a href="#">Edit Profile</a></li>
                  <li><a href="#">Privacy</a></li>
                  <li role="separator" class="divider"></li>
                  <li><a href="/user/logout">Log Out</a></li>
                </ul>
              </li><!--/.dropdown -->
            </ul>
            <div class="col-lg-4 col-lg-offset-1 col-md-4 col-md-offset-1 col-sm-4">
              <form class="navbar-form" role="search">
                <div class="form-group" style="display:inline;">
                  <div class="input-group" style="position: relative">
                    <input id="autocomplete-ajax" type="text" class="form-control" placeholder="Search for...">
                    <input type="text" name="country" id="autocomplete-ajax-x" disabled="disabled" style="color: #CCC; position: absolute; background: transparent; z-index: 1;"/>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                  </div>
                </div>
              </form>
            </div>
          </div><!--/#navbar -->
        <?php
        } ?>
      </div>
    </nav>
    <div id="main">

    <?php require_once $this->viewFile;?>

    </div> <!-- #main -->
</div><!-- #app .container -->
<script type="text/javascript" src="/assets/js/jquery.autocomplete.js"></script>
<script>
  $('#autocomplete-ajax').autocomplete({
    lookup: function(query,done) {
      var result = {
        suggestions:[]
      };

      $.ajax({
        type: "POST",
        url: "/api/search",
        crossDomain: true,
        data: JSON.stringify(query),
        dataType: 'json',
        success: function(responseData, textStatus, jqXHR) {
          //console.dir(responseData);
          $(responseData).each(function (i) {
            result.suggestions.push(responseData[i]);
          });
          done(result);
        },
        error: function (responseData, textStatus, errorThrown) {
          alert('POST failed.');
        }
      });
    },
    onSelect: function(suggestion) {
      document.location.href="/user/" + suggestion.data;
    },
    onHint: function (hint) {
      $('#autocomplete-ajax-x').val(hint);
    },
    onInvalidateSelection: function() {
//      $('#selction-ajax').html('You selected: none');
      alert('selected nothing');
    }
  });
</script>
</body>
</html>
