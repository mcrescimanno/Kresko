<?php session_start() ?>
<!DOCTYPE html>
<html>
  <head>
    <script src = "../includes/validate.js"></script>
    <?php include "../includes/header.php"; ?>
    <?php include "../config.php"; ?>
  </head>
  <body>
    <div class='container-fluid page-wrapper'> <!-- This will wrap the entire page: allows us to use bootstrap rows and columns -->
      <?php include "../includes/navigation.php"; ?>
        <h2 class="center white">Artisans</h2>
        <?php
          require_once '../config.php';
          $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
          if ($mysqli->errno) {
            print($mysqli->error);
            exit();
          }
          $query = "SELECT * FROM vendors";
          $result = $mysqli->query($query);
          if (isset($_SESSION['logged_usertype'])&&$_SESSION['logged_usertype']==1) { ?>
          <form action="artisans.php" method="post" class="artisan">
            <p class="white">Delete an Artisan</p> <br>
            <select name="vendorid">
              <?php
                while($row=$result->fetch_assoc()) {
                  print("<option value='{$row['vendorid']}'>{$row['vendorname']}</option>");
                }
              ?>
            </select> <br>
            <input type="submit" value="Delete Artisan" name="deletevendorbutton" id="deletevendorbutton">
          </form>
          <form onsubmit="return validateAddVendor(this);" action = "artisans.php" method="post" class="artisan" enctype="multipart/form-data"> 
            <p class="white">Add an Artisan</p>
            <p class="white">Artisan Name: </p> <input type="text" name="artisanname" id="artisanname"/>
            <p class="white">Artisan E-mail: </p> <input type="text" name="artisanemail" id="artisanemail"/> <br>
            <p class="white">Artisan Password: </p> <input type="text" name="artisanpw" id="artisanpw"/> <br>
            <p class="white">Artisan Description: </p><textarea rows="4" name="artisandesc" id="artisandesc"> </textarea><br>
            <span class='white'>Modify vendor image:</span> <input class="white" id="new-photo" type="file" name="newphoto"/> <br>
            <input type="submit" value="Add Artisan" name="addvendorbutton" id="addvendorbutton">
          </form>
          <?php
          }
            $result= $mysqli->query($query);
            if ($result->num_rows == 0) {
              print("There are no Artisans available!");
            } else {
                while ($row = $result->fetch_assoc()) {
                  print("<div class='content-artisans'>
                    <div class='vendor'>
                        <img class='vendor-image' src={$row['filepath']} alt='artisan-image'/>
                          <div class='vendor-info'>
                          <a href='./profile.php?vendorID={$row['vendorid']}'<h3 class='white'>{$row['vendorname']}</h3></a>
                          <p class='white'>{$row['description']}</p>
                          </div>
                          </div>
                          </div>");
                }
            }

            if (isset($_POST['deletevendorbutton'])) {
              $vendorid=$_POST['vendorid'];
              $result=$mysqli->query("SELECT userid from vendors where vendorid=$vendorid");
              while($row=$result->fetch_assoc()) {
                $id=$row['userid'];
                echo($id);
                $mysqli->query("DELETE FROM vendors where vendorid=$vendorid");
                $mysqli->query("DELETE FROM users where userid=$id");
                $mysqli->query("DELETE from items where vendorid=$vendorid");
              }
            }

            if (isset($_POST['addvendorbutton'])) {
              $query= "INSERT INTO `users`(`userid`, `username`, `email`, `creation_date`, `pw`, `usertype`) VALUES (null,'{$_POST['artisanname']}','{$_POST['artisanemail']}',null,'{$_POST['artisanpw']}',2)";
              echo($query);
              if ($mysqli->query($query)===TRUE) {
                $id=$mysqli->insert_id;
                if (!empty($_FILES['newphoto'])) {
                  $newfile=$_FILES['newphoto'];
                  $tempName=$newfile['tmp_name'];
                  $name=$newfile['name'];
                  $location='../images/vendors/'.$name;
                  move_uploaded_file($tempName, $location);
                  $query="INSERT INTO vendors(vendorid, description, email,filepath, userid,vendorname) VALUES (null,'{$_POST['artisandesc']}','{$_POST['artisanemail']}', '$location', $id,'{$_POST['artisanname']}')";
                  $mysqli->query($query);
                } else {
                  $query="INSERT INTO vendors(vendorid, description, email,filepath, userid,vendorname) VALUES (null,'{$_POST['artisandesc']}','{$_POST['artisanemail']}', null, $id,'{$_POST['artisanname']}')";
                  $mysqli->query($query);
                }
              }
            }
          ?>

      <div class="footer">
        <?php include "../includes/footer.php"; ?>
      </div>
    </div>
  </body>
</html>