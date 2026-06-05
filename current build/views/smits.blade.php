@extends('layouts.master')
@section('content')
  
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Smits BOC Integration Test</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Smits BOC Integration Test</li>
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
            
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
        <div id="aloha_chat_div_test_object" class="chatbox"></div>
      </div><!-- /.container-fluid -->
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
  <script src=https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js></script>

  <script type="text/javascript" src=https://applications.apac2.quandago.app/js/api/aloha-2.0.js></script>

  <script>

    document.addEventListener("DOMContentLoaded", function () {
      console.log('Script loading...');
      aloha.setDesktopDomain('https://desktop.apac2.quandago.app');
      // aloha.setDesktopDomain('https://ccaas-fb.tritelcares.com');

      aloha.subscribeNotifications(aloha.incoming_call | aloha.current_status | aloha.incoming);

      aloha.setNotifyHandler(function (event) {
        
        console.log('Event received:', event);
        

        switch  (event.type) {

          case aloha.incoming_call:

            setTimeout(function () {

              let currentStatus = aloha.requestStatus();

              console.log('tags', currentStatus);

            }, 3000);

            break;

        }

        const phoneNumber = retrieveTagValue(event, 'CallerId');
        conole.log('Retrieved Phone Number:', phoneNumber);

        if (phoneNumber) {

          console.log('Phone Number:', phoneNumber);

        }

      });

 

      const retrieveTagValue = (event, tagName) => {

        let output = "";

        for (let i = 0; i < event.status.tags.length; i++) {

          for (key in event.status.tags[i]) {

            if (event.status.tags[i].tag.indexOf(tagName) != -1) {

              output = event.status.tags[i].tag.replace(tagName, '');

            }

          }

        }

        return output.trim().replaceAll('+', " ");

      }

    });

    console.log('Script fully loaded...');

  </script>
@endsection
  
