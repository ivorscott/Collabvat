<?php $user = new User(); ?>
<div id="setup-pane">
  <div id="setup">
    <h2 class="title">New Presentation</h2>
    <form id="upload" action="/classes/Uploader.php" method="post" enctype="multipart/form-data">
      <p><strong>Room Name</strong></p>

      <p class="inputField">
        <input id="roomName" type="text" name="title" placeholder="My Presentation" autocomplete="off"/>
        <small id="roomName-defaultName">default: My Presentation</small>
      </p>

      <p><strong>Guests</strong></p>

      <div class="input-group" style="position: relative">
          <input id="autocomplete-guest" class="autocomplete" type="text" class="form-control" placeholder="Type guest name ...">
          <input type="text" name="country" id="autocomplete-guest-x" disabled="disabled"/>
          <div id="invites">
          </div>
      </div>

      <label for="file-upload" class="custom-file-choose">
        <i class="glyphicon glyphicon-cloud-upload"></i> Choose Artworks
      </label>
      <input id="file-upload" type="file" name="media[]" multiple="multiple"/>
      <input id="upload-btn" class="custom-file-upload btn-primary" type="submit" value="Upload Art" />
      <div id="progress-div"><div id="progress-bar"></div></div>
      <div id="continue">Start Critique</div>

    </form>
    <div id="media-assets"></div>
  </div>
</div>

<?php
  $chat = array();
  $data['creator'] = 'Andy Warhol';
  $data['users'] = array('David Kelly','Mark Rothko','Jennie Simms','Jeff Koons', 'Andy Warhol');
  $chat['room'][] = $data;
  $data['creator'] = 'Richard Prince';
  $data['users'] = array('Kerry Bee','Koko Mo','John Mack','Maria Glee','Any Lee','Molly Smith','Kimberly Lake',
              'Gerry Berry', 'Han Solo','Dark Vader','Eric Clapton','Richard Prince');
  $chat['room'][] = $data;
  $data['creator'] = 'Damien Hirst';
  $data['users'] = array('Derek Sick','Matthew Lisp','Imman Bliss','Jeoo Ping',
              'Hilary Kent','Danny Naster','William Smacks','Damien Hirst');
  $chat['room'][] = $data;
  $r = '';

  foreach ($chat['room'] as $data) {
    $r .= '<div class="room">';
    $r .= '<footer>';
    $r .= '<a class="user" href="#">' . $data['creator'] . '</a>';
    $r .= '<a class="guests"><i class="glyphicon glyphicon-user"></i>' . sizeof($data['users']) . '</a>';
    $r .= '</footer>';
    $r .= '</div>';
  }
?>
<div id="chatroom-pane" class="chat-rooms">
  <h2>Active Rooms</h2>
  <div id="selectroom"><?php echo $r; ?></div>
  <div id="chatControls">
    <button id="prev">Prev</button>
    <button id="next">Next</button>
    <form>
      <div id="captionWrapper">
        <input autocomplete="off" id ="caption" type="text" placeholder="Untitled, oil on canvas, 120cm x 150cm, 2015 " />
        <button id="save">Save</button>
      </div>
    </form>
  </div>
  <div id="nameDiv"></div>

   <script>
    var roomName = document.getElementById('roomName'),
        nameDivElement = document.getElementById('nameDiv');
    roomName.addEventListener('keyup', function(){
      var text = roomName.value;
      nameDivElement.innerHTML = text;
    });
   </script>
</div>

<script type="text/javascript">

$(function() {

 window.guests = [];

  // chatroom guest autocomplete
  $('#autocomplete-guest').autocomplete({
    lookup: function(query,done) {
      // store  results
      var result = {
        suggestions:[]
      };

      // find suggestions
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
      guests.push(suggestion.id);
      $('#invites').show();
      $('#invites').append("<p><img src='/assets/images/placeholder-user.png'>"+suggestion.value+"</p>");
      $('#autocomplete-guest').val("");
    },
    onHint: function (hint) {
      $('#autocomplete-guest-x').val(hint);
    }
  });

  // hide progress bar initially
  $("#progress-div").hide();
  $("#upload").change(function(){
    $("#upload-btn").show()
  });
;
  $("#upload").submit( function(e) {
    e.preventDefault();
    // Wait until images are selected
    if($('#file-upload').val()) {
      $("#upload-btn").blur()
      //Upload images
      $(this).ajaxSubmit( {
        target:   '#media-assets',
        beforeSubmit: function() {
          $("#progress-div").show();
          $("#progress-bar").width('0%');
          $( "#media-assets" ).fadeOut( "200" );
        },
        uploadProgress: function (event, position, total, percentComplete) {
          $("#progress-bar").width(percentComplete + '%');
        },
        success: function (data) {
          $( "#media-assets" ).fadeIn( "200" );
          $( "#submit" ).fadeOut( "200" );
          $( "#continue" ).fadeIn( "200" );
          var imageArray, currentImageId = '', counterForImage = 0,
              thumbnails = $('.thumb'), captions = [];
          imageArray = saveImageCollection();
          addEvents();
          $('.thumb:first').trigger('click');
          $('#continue').css({'color':'#fff','background':'#7647a2'});
          $('#continue').hover(function(){
            $(this).toggleClass('ready');
          });

          function addEvents() {
            thumbnails.on('click',function() {
              // display chat controls
              $('#chatControls').css('display','block');
              // get the path of the full size image
              var formattedImages = formatImages(this);
              // keep track of the current image on display
              currentImageId = $(this).attr('data-id');
              // create the gallery editor view
              $('#selectroom').html(
                '<div id="fullViewWrapper">' + '<img class="fullView" ' +
                'data-id="' + currentImageId + '" src="' + formattedImages[1] + '" /></div>'
              );

              var index = getImageIndex();
              if(imageArray[index].caption != '') {
                $('#caption').val(imageArray[index].caption);
              }
            });

            $('#caption').on('keypress', function() {
              // reveal the save button
              $('#save').css('background-color', '#4Af');
              $('#save').fadeIn( "slow", function() {
                // save caption value by storing it in imageArray
                $(this).on('click',function(e) {
                  $(this).fadeOut(200, function() {
                    $(this).css('background-color', '#00B800').fadeIn(200);
                  });
                  e.preventDefault();
                  var index = getImageIndex();
                  imageArray[index].caption = $('#caption').val();
                });
              });
            });

            $('#next').on('click',function() {
              $('#caption').val("");
              counterForImage = counterForImage + 1;
              swapImage();
            });

            $('#prev').on('click',function() {
              $('#caption').val("");
              counterForImage = counterForImage - 1;
              swapImage();
            });

            $('#continue').off();
            $('#continue').on('click', function() {
              var roomName = $('#roomName').val();
              saveCaptionCollection();
              goToRoom(roomName);
            });
          }// addEvents()

          function goToRoom(roomName) {
            var room = {
              name: roomName,
              user_id: <?= $user->model->id;?>,
              image_ids: getImageIds(),
              guest_ids: getGuestIds()
            };
            // send to chat controller
            $.ajax({
              method: "POST",
              url: "/chat/setRoom",
              data: JSON.stringify(room),
              contentType: "application/json",
              success: function(room) {
                window.location.href = "chat/room/" + room;
              }
            });
          }

          function saveImageCollection() {
            var collection = [];
            // build an object per image
            thumbnails.each(function(i) {
              var formattedImages = formatImages(this);
              console.log(this.src)
              var image = {
                // use php to get user id
                user_id : <?= $user->model->id;?>,
                thumb_path : formattedImages[0],
                full_path : formattedImages[1]
              };
              collection.push(image);
            });
            // send to chat controller
            $.ajax({
              method: "POST",
              url: "/chat/saveImageCollection",
              data: JSON.stringify(collection),
              contentType: "application/json",
              success: function(data) {
                var image = JSON.parse( data );
                // reset collection
                while(collection.length > 0) {
                    collection.pop();
                }
                thumbnails.each(function(i) {
                  // fill collection with response
                  collection.push(image[i]);
                  // give thumbnail data-id attribute, containing image id
                  $(this).attr('data-id', image[i].id);
                });
              }
            });
            return collection;
          }

          function saveCaptionCollection() {
            // send to chat controller
            $.ajax({
              method: "POST",
              url: "/chat/saveCaptionCollection",
              data: JSON.stringify(imageArray),
              contentType: "application/json"
            });
          }

          function getImageIds() {
            var result = '';
            $(imageArray).each(function(i) {
              result += imageArray[i].id + ' ';
            });
            return $.trim(result);
          }

          function getGuestIds() {
            var result = '';
            $(window.guests).each(function(i) {
              result +=window.guests[i] + ' ';
            });
            return $.trim(result);
          }

          function formatImages(el) {
            var array = [];
            var strBase = "/models/images";
            var thumb = el.src.split(strBase);
            array.push(strBase+thumb[1]);
            var full = thumb[1].split("_th.");
            array.push(strBase+full[0]+'.'+full[1]);
            return array;
          }

          function getImageIndex() {
            for (var i = 0; i < imageArray.length; i++) {
              if (imageArray[i].id == currentImageId) return i;
            }
          }

          function updateImage(index) {
            console.log(imageArray[index].full_path);
            $('.fullView').attr('src', imageArray[index].full_path);
            $('.fullView').attr('data-id', imageArray[index].id);
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
              if(imageArray[index].caption != '') {
                $('#caption').val(imageArray[index].caption);
              }
              // hide save button
              $('#save').hide();
            }
          }
        }
      });
    }
  });
});
</script>
