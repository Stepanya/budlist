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
          <h1 class="m-0">SMS Blast Demo</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">SMS Blast Demo</li>
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
              <h5 class="m-0">SMS blast form</h5>
            </div>
            <div class="card-body">
              <h6 class="card-title"></h6>
              <form id="sms-form">
                @csrf
                <div class="form-group">
                  <label>Schedule</label>
                  <div class="input-group date" data-target-input="nearest">
                    <input type="text" id="datetime" class="form-control datetimepicker-input" data-target="#reservationdatetime">
                    <div class="input-group-append" data-target="#reservationdatetime" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="exampleInputFile">Recepients</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="recepients-input">
                      <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                    </div>
                    <div class="input-group-append">
                      <span class="input-group-text">Upload</span>
                    </div>
                  </div>
                </div>
                <button href="#" class="btn btn-primary" id="send-btn">Send</button>
              </form>
            </div>
          </div>
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

<script>
  $(function () {
    //Date picker
    const picker = new tempusDominus.TempusDominus(document.getElementById('datetime'), {
      display: {
        icons: {
          type: 'icons',
          time: 'fa-solid fa-clock',
          date: 'fa-solid fa-calendar',
          up: 'fa-solid fa-arrow-up',
          down: 'fa-solid fa-arrow-down',
          previous: 'fa-solid fa-chevron-left',
          next: 'fa-solid fa-chevron-right',
          today: 'fa-solid fa-calendar-check',
          clear: 'fa-solid fa-trash',
          close: 'fa-solid fa-xmark'
        },
        theme: 'light',
      }
    });
    
    $('#sms-form').submit(function(e) {
      e.preventDefault(); // Prevent the default form submission

      // Get the values from the input fields
      var recepients = $('#recepients-input')[0].files[0];
      var datetime = $('#datetime').val();
      // Get the CSRF token value
      var csrfToken = $('input[name="_token"]').val();

      // Create a FormData object and append the values
      var formData = new FormData();
      formData.append('recepients', recepients);
      formData.append('datetime', datetime);
      formData.append('_token', csrfToken);
 
      // Perform the AJAX POST request
      $.ajax({
        url: '/sms-blast/send',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          // Handle the success response
          console.log('Request successful');
          console.log(response);
        },
        error: function(xhr, status, error) {
          // Handle the error response
          console.log('Request failed');
          console.log(error);
        }
      });
    });
  })
</script>
@endsection

