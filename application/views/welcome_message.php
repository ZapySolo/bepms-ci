<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Runner onepage - html</title>
  <meta name="keywords" content="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.">
  <meta name="description" content="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.">

  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/fonts/flat-icon/flaticon.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div id="content-wrapper">
    <header class="header header--bg">
      <div class="container">
        <nav class="navbar">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span> 
            </button>
            <a class="navbar-brand" href="#"></a>
          </div>
          <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav pull-right">
              <li><a href="#">HOME</a></li>
              <li><a href="#">API</a></li>
              <li><a href="#">ABOUT</a></li>
              <li><a href="tel:7774072857">CONTACT</a></li>
            </ul>
          </div>
        </nav>
        <div class="row">
          <div class="col-lg-6">
            <img class="img-responsive" src="assets/images/running-man.png" alt="" width="500">
          </div>
          <div class="col-lg-6 header__content">
            <h1 class="title">B.E Project Management System <br> <span class="title-style"></span></h1>
            <p>The most valuable project vault.<br> Optimizing the process of project management.</p>
            <a class="header__button" href="#">LEARN MORE</a>
            <a class="header__button" href="#">LOGIN</a>
          </div>
        </div>
      </div>
    </header>
  </div>

  <script src="assets/jquery/jquery-3.2.1.min.js"></script>
  <script src="assets/bootstrap/js/bootstrap.min.js"></script>
  <!-- <script src="assets/owl-slider/owl.carousel.min.js"></script> -->

  <script>
    $(document).ready(function() {
      
      $('button').click( function(e) {
        $('.collapse').collapse('hide');
      });
      
    });
  </script>
</body>
</html>  

