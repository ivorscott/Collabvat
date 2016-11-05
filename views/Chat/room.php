<?php
// get room
$room = new RoomModel();
$room->find('hash', htmlentities($_GET['id']));
$roomTime = ($room->timestamp) ? $room->timestamp : 0 ;
?>

<script>
  $(function() {
    // Set the user for chat widget
    var theUser = {
      id: "<?php echo $user->model->id; ?>",
      name: "<?php echo $user->model->username; ?>",
      email: "<?php echo $user->model->email; ?>"
    };
    var timezone = "<?php echo $user->model->timezone; ?>";
    var roomId = "<?php echo $room->id;?>";
    // Initialize pusher.js chat widget
    var history = <?php echo json_encode($model['activity'], JSON_PRETTY_PRINT)?>;
    var chatWidget = new PusherChatWidget(pusher,{appendTo:"#pusher_chat_widget"},theUser,timezone,roomId,history);
  });
</script>
<div id="room-pane">
  <div id="room">
    <button id="prev">Prev</button>
    <button id="next">Next</button>
    <div id="media">
      <img class="fullView" data-id="" src="/assets/images/placeholder.png">
    </div>
    <aside class="room-author">
      <div class="details-wrapper">
        <div class="details-header">
        <?php
          $creator = new UserModel();
          $creator->find('id',$room->user_id);
          $placeholder_image = '/assets/images/placeholder.png';
          $grav_url = get_gravatar($creator->email, $placeholder_image);
        ?>
         <img src="<?php echo $grav_url; ?>" alt="" width="80" height="80"/>
         <div class="details">
           <p><?php echo $room->room_name;?></p>
           <span id="countdowntimer">00:00</span> <small id="countdowntext" style="color:#bbb;">mins/sec</small>
         </div>
        </div>
        <div class="thumb-nav">
          <?php
          $r ="";
          $r.="<ul>";
          // convert string to array
          $imageIds = explode(" ", $room->image_ids);
          foreach($imageIds as $id){
            $image = new ImageModel();
            $image->find("id",$id);
            $r.="<li><img class='thumb' data-id='{$id}' src='{$image->thumb_path}'></li>";
          }
          $caption =  "{$image->caption}";
          $r.="<li><span class='glyphicon glyphicon-plus'></span></li>";
          $r.="</ul>";
          echo $r;
          $r.="<li><img class='thumb' data-id='{$id}' src='{$image->thumb_path}'></li>";
          ?>
        </div>
			 </div>
		 </aside>
	</div>
</div>
<div id="chatroom-pane">
  <div id="pusher_chat_widget"></div>
</div>
<script>
  $(function(){
    var imageArray, currentImageId = '', counterForImage = 0,
      thumbnails = $('.thumb'), captions = [];
    imageArray = thumbnails;
    addEvents();
    $('.thumb:first').trigger('click');

    function addEvents() {
      thumbnails.on('click',function() {
        var src = getFullImage(this);
        // keep track of the current image on display
        currentImageId = $(this).attr('data-id');
        // create the gallery editor view
        updateCurrentImage();
        var fullimage = getFullImage(this);
        $('.fullView').attr("src", fullimage);
        $('.fullView').attr('data-id',currentImageId);
//      if(imageArray[index].caption != '') {
//        $('#caption').val(imageArray[index].caption);
//      }
      });

      $('#next').on('click',function() {
        // $('#caption').val("");
        counterForImage = counterForImage + 1;
        swapImage();

      });

      $('#prev').on('click',function() {
        // $('#caption').val("");
        counterForImage = counterForImage - 1;
        swapImage();
      });
    }

    function getImageIds() {
      var result = '';
      $(imageArray).each(function(i) {
        result += imageArray[i].id + ' ';
      });
      return $.trim(result);
    }

    function getFullImage(el) {
      var parseSource = el.src.split("_th.");
      return parseSource[0] + '.' + parseSource[1];
    }

    function updateCurrentImage() {
      for (var i = 0; i < imageArray.length; i++) {
        if (imageArray[i].id == currentImageId) {
          currentImageId = imageArray[i].id;
        }
      }
    }

    function updateImage(index) {
      var img = $(imageArray[index]);
      var fullpath = getFullImage(img[0]);
      $('.fullView').attr('src', fullpath);
      $('.fullView').attr('data-id', img.attr('data-id'));
      currentImageId = imageArray[index].id;
    }

    function swapImage() {
      // anthony torrie's circular list magic
      counterForImage = counterForImage % (imageArray.length);
      var inverseCounterImage = -1;
      if (counterForImage > -1) {
        inverseCounterImage = 1;
      }
      var index = counterForImage * inverseCounterImage;
      // if the image object exists
      if (imageArray[index]) {
        updateImage(index);
        // fill input field with caption if it exists
//      if(imageArray[index].caption != '') {
//        $('#caption').val(imageArray[index].caption);
//      }
      }
    }
  });
</script>
<script type="text/javascript">
  var roomTime =  <?php echo $roomTime; ?>;

  if (roomTime === 0) {
    // initialize timestamp for the first time
     var sTime = new Date().getTime();
    // build object with room id, and timestamp
    var room = { hash: "<?php echo htmlentities($_GET['id']); ?>", time: sTime };
    // send room object to set timestamp
    $.ajax({
      method: "POST",
      url: "/chat/setTime",
      data: JSON.stringify(room),
      contentType: "application/json"
    });// ajax
  } else {
    // use original timestamp
    var sTime = <?php echo $roomTime ?>;
  }
  // Number of miliseconds to count down from.
  var countDown = 1200;
  function UpdateCountDownTime() {
    var cTime = new Date().getTime();
    var diff = cTime - sTime;
    var timeStr = '';
    var seconds = countDown - Math.floor(diff / 1000);

    if (seconds >= 0) {
      var hours = Math.floor(seconds / 3600);
      var minutes = Math.floor( (seconds-(hours*3600)) / 60);
      seconds -= (hours*3600) + (minutes*60);

      if (hours < 10) {
        timeStr = "0" + hours;
      } else {
        timeStr = hours;
      }
      if (minutes < 10) {
        timeStr = timeStr + ":0" + minutes;
      } else {
        timeStr = timeStr + ":" + minutes;
      }
      if (seconds < 10) {
        timeStr = timeStr + ":0" + seconds;
      } else {
        timeStr = timeStr + ":" + seconds;
      }
      document.getElementById("countdowntimer").innerHTML = timeStr;
    } else {
      var room = { hash: "<?php echo $_GET['id']; ?>" };
      $.ajax({
        method: "POST",
        url: "/chat/closeRoom",
        data: JSON.stringify(room),
        contentType: "application/json",
        success: function(data) {
          document.getElementById("countdowntimer").style.display="none";
          document.getElementById("countdowntext").innerHTML = "CLOSED";
          clearInterval(counter);
        }
      });
    }
  }
  UpdateCountDownTime();
  var counter = setInterval(UpdateCountDownTime, 500);
</script>
