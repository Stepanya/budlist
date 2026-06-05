@extends('layouts.master')
@section('content')
  <style>
    .aloha-panel {
      position: fixed;
      bottom: 45px!important;
      right: 20px!important;
      width: 350px;
    }
    .aloha-panel-default>.aloha-panel-heading { 
      background-color: #337ab7!important;
      border-radius: 5px 5px 0 0!important;
    }
    
    
  </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Webchat</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Webchat</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- /.col-md-6 -->
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header">
                <h5 class="m-0">Chat button</h5>
              </div>
              <div class="card-body">
                <h6 class="card-title"></h6>

                <p class="card-text">Click the button below to start chat. </p>
                <a href="#" class="btn btn-primary" id="chat-btn" data-toggle="modal" data-target="#webchat-confirm-modal">Chat us!</a>
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
        <div id="aloha_chat_div_test_object" class="chatbox"></div>
      </div><!-- /.container-fluid -->
    </div>
    <!--Confirmation Modal -->
    <div class="modal fade" id="webchat-confirm-modal" data-backdrop="static" data-keyboard="false" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Confirm</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <p>Are you sure you want to chat with us?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="confirm-yes" class="btn btn-primary" data-dismiss="modal">Yes</button>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->
  <script src="https://cdn.vanadcloud.com/chat/1.9/js/script.min.js"></script>
  <script>
    //initializes webchat object
    alohaWebChat = new AlohaWebChat({
      server: 'https://chat.tritelcares.com/v2',
      // server: 'https://chat.prosync.sanmiguel.com.ph',
      // server: 'https://chatapi.apac2.quandago.app/v2',
      // chatbox: 'lbc_trichat',
      // chatbox: 'poc_prosyncwebchat',
      // chatbox: 'poc_trichat',
      // chatbox: 'poc_webchattritel',
      // chatbox: 'lbc_lbcwebchatph',
      chatbox: 'smits_tritelwebchat',
      debug: true,
      chat_obj: 'aloha_chat_div_test_object',
      tags: ['tag1', 'tag2', 'currenturl: https://www.website.com'],
      labels: {
        'title': 'Live chat',
        'close_tooltip': 'Close',
        'minimize_tooltip': 'Collapse',
        'maximize_tooltip': 'Expand',
        'send_button_label': 'Send',
      },
      // rejectUnauthorized: false Ignore SSL certificate errors
    });

    function startChat() {
		
      // starts/shows the real chat box
      alohaWebChat.startAlohaWebChat('Acme user', 'acme@vanadgroup.com');
      alohaWebChat.settags(['tag3', 'tag4', 'newurl: ' + window.location.href]);
      
      //when the chatbox successfully loads, the message is inserted to the real chat box, the submit button is clicked and the fake chat is hidden
      alohaWebChat.addAlohaNotifyHandler('joinSuccess', function() {
        
      });
      alohaWebChat.addAlohaNotifyHandler('createUserSuccess', function() {
        
      });

	  }

    $('#confirm-yes').click(() => {
      startChat()
    })
  </script>
@endsection
  
