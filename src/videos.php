<?php
require('cred.php');

$connect = new mysqli($host, $user, $pd, $db);
if ($connect->connect_errno) {
  echo "Connection to database failed. \n";
  echo $connect->connect_errno.": ".$connect->connect_error;
}
/*
this connects to the database, connection code is based on lines 1-6 in Example #1 on us2.php.net/manual/en/mysqli.quickstart.prepared-statements.php
*/
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset ="UTF-8">
  </head>
  <body>
    <p>
      <h3>Add a Video</h3>
      <form method="POST" action="videos.php">
        <p>
          <label for="vName">Name: </label>
          <input type="text" name="name" id="vName">
        <p>
          <label for="vCategory">Category: </label>
          <input type="text" name="category" id="vCategory">
        <p>
          <label for="vLength">Length: </label>
          <input type="number" name="length" id="vLength">
        <p>
          <input type="submit" value="Add Video">
        </p>
      </form>
<?php
$flag1 = false;

if (isset($_POST['name'])|| isset($_POST['category']) || isset($_POST['length'])) {
  if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['length'])) {
    echo "Please fill in all required fields";
    $_POST = array(); // empty the POST array
  }
  else {
    if(!ctype_digit($_POST['length'])) {
      echo "Length must be a numerical value";
      $_POST = array();
    }
    else {
      if ($_POST['length'] < 0) {
        echo "Length must be a value greater than 0.";
        $_POST = array();
      }
      else {
        $flag1 = true;
      }
    }    

  }

}

if ($flag1 === true) {
  $name=$_POST['name'];
  $category=$_POST['category'];
  $length=$_POST['length'];

  if (!($add = $connect->prepare("INSERT INTO inventory(name, category, length) VALUES (?, ?, ?)"))) {
    echo "Insert Preparation Error";
  }
 
  if (!($add->bind_param("ssi", $name, $category, $length))) {
    echo "Binding Parameters Error";
  }

  if (!($add->execute())) {
    echo "Execute Error";
  }
// the three if statements above add movies to the database 
  $add->close();
  $flag1 = false;
}

?>
    </p>
    <p>
      <h3>Delete All Videos</h3>
      <form method="POST" action="videos.php">
        <input type="submit" name="deleteAll" value="Delete All">
      </form>
    </p>
<?php
if (isset($_POST['deleteAll']) && $_POST['deleteAll']== "Delete All" ) {
 
  if (!($del = $connect->prepare("DELETE FROM inventory"))) {
    echo "Insert Preparation Error";
  }
 
  if (!($del->execute())) {
    echo "Execute Error";
  }
// this deletes ALL the videos from the database 

  $del->close();
  $_POST = array(); // clear the array just in case
}

if (!($count = $connect->prepare("SELECT COUNT(*) FROM inventory"))) {
  echo "Insert Preparation Error";
}
 
if (!($count->execute())) {
  echo "Execute Error";
}

if (!($count->bind_result($number))) {
  echo "Error retrieving results";
}

$count->fetch();
// this code above will analyze how many entries are in the table

$count->close();

if ($number > 0) {

  echo "<table border=1>";

  if (!($ret = $connect->prepare("SELECT id, name, category, length, rented FROM inventory"))) {
    echo "Insert Preparation Error";
  }
 
  if (!($ret->execute())) {
    echo "Execute Error";
  } 

  if (!($ret->bind_result($vId, $vidN, $vidC, $vidL, $vidR))) {
    echo "Error retrieving results";
  }

  while ($ret->fetch()) {

    //if ($vidR == 1)

    echo "<tr><td>$vidN<td>$vidC<td>$vidL<td>$vidR<td><form method=\"POST\" action=\"videos.php\"><input type=\"hidden\" name=\"id\" value=\"$vId\"><input type=\"submit\" name=\"deleteOne\" value=\"Delete\"></form></td></tr>";
  }
  echo "</table>";
  
  $ret->close();
// the code here should fetch the rows - fetching all rows for now to make sure code works

  if (isset($_POST['id'])) {
    $vidId =  $_POST['id'];
    if (!($delO = $connect->prepare("DELETE FROM inventory WHERE id=(?)"))) {
      echo "Insert Preparation Error";
    }
 
    if (!($delO->bind_param("i", $vidId))) {
      echo "Binding Parameters Error";
    }

    if (!($delO->execute())) {
      echo "Execute Error";
    } 

    $delO->close();

    $_POST = array();
  }
  // this deletes the video based on its id

}

?>
  </body>
</html>
