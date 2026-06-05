<!DOCTYPE html>
<!--
  This is a starter template page. Use this page to start your new project from
  scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sample Webchat</title>
  
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdn.vanadcloud.com/chat/1.8/css/style.min.css">
  <link rel="stylesheet" href="css/all.css">
  
  
  <!-- REQUIRED SCRIPTS -->
  <script src="js/all.js"></script>
  <!-- Popperjs -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <!-- Tempus Dominus JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.7.7/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
  
  <!-- Tempus Dominus Styles -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.7.7/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
</head>
<body class="hold-transition sidebar-mini sidebar-collapse">
  <div class="wrapper">
    
    @include('layouts.nav-bar')
    @include('layouts.side-bar')
    
    @yield('content')
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
      <div class="p-3">
        <h5>Title</h5>
        <p>Sidebar content</p>
      </div>
    </aside>
    <!-- /.control-sidebar -->
    
    <!-- Main Footer -->
    <footer class="main-footer">
      <!-- To the right -->
      <div class="float-right d-none d-sm-inline">
        Webchat
      </div>
      <!-- Default to the left -->
      <strong>Copyright &copy; 2022 <a href="http://tritelph.com/">Tritel Communications Inc</a>.</strong> All rights reserved.
    </footer>
  </div>
  <!-- ./wrapper -->
  
</body>
</html>