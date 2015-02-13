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

  $category = strtolower($category); // makes all letters lowercase to avoid duplication errors later
 
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
/* deletes ALL videos */
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
/* deletes ALL videos */

/* print out videos if they exist */
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

echo "<h3>Current Movies in Inventory</h3>";

if ($number > 0) {

  echo "<form method=\"POST\" action=\"videos.php\"><select name=\"selCat\">";

  $categories = array();

  if (!($cat = $connect->prepare("SELECT category FROM inventory"))) {
    echo "Insert Preparation Error";
  }
 
  if (!($cat->execute())) {
    echo "Execute Error";
  } 

  if (!($cat->bind_result($vidCat))) {
    echo "Error retrieving results";
  }

  while ($cat->fetch()) {
    $flagS = false;

    $vidCat = ucwords($vidCat); // capitalizes first letter
// this makes sure that all letters in the same words have the same case
// minimizes case-sensitive issue with comparisons

    if (count($categories) === 0) {
      $categories[] = $vidCat;
    }
    else {
      foreach ($categories as $value) {
        if ($value == $vidCat) {
          $flagS = true;
        }
      }

      if ($flagS === false) {
          $categories[] = $vidCat;
      }
    }
  }

  if (count($categories) !== 0) {
    foreach ($categories as $value) {
      echo "<option value=\"$value\">$value</option>";
    }
echo "<option value=\"all\">All Movies</option></select>";
echo "<input type=\"submit\" value=\"Filter\"></form>";
  }
  else {
    echo "Array Error"; // this should never execute, but put here just in case
  }

  $cat->close();
// the above code takes in category input
// the code below will print out the appropriate movies

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

  if (isset($_POST['check'])) {
    $vidId2 =  $_POST['check'];
    if (!($rent = $connect->prepare("SELECT rented FROM inventory WHERE id=(?)"))) {
      echo "Insert Preparation Error";
    }
 
    if (!($rent->bind_param("i", $vidId2))) {
      echo "Binding Parameters Error";
    }

    if (!($rent->execute())) {
      echo "Execute Error";
    } 

    if (!($rent->bind_result($status))) {
      echo "Error retrieving results";
    }

    $rent->fetch();
    $rent->close();
// first this checks to see what the value of rented is - 0 or 1 (false or true)
// then it will change the value accordingly

    if ($status == 1) {
      if (!($rent = $connect->prepare("UPDATE inventory SET rented = 0 WHERE id=(?)"))) {
        echo "Insert Preparation Error";
      }
    }
    else {
      if (!($rent = $connect->prepare("UPDATE inventory SET rented = 1 WHERE id=(?)"))) {
        echo "Insert Preparation Error";
      } 
    }

    if (!($rent->bind_param("i", $vidId2))) {
      echo "Binding Parameters Error";
    }

    if (!($rent->execute())) {
      echo "Execute Error";
    } 
    $rent->close();

    $_POST = array();
  }
  // this updates the video based on its id


  echo "<table cellpadding=5px>";
  echo "<tr><th>Name<th>Category<th>Length (min)<th>Status<th>Check In/Out<th>Delete</th></tr>";

  if (isset($_POST['selCat'])) {

    $selectedCat = strtolower($_POST['selCat']);
    $_POST = array(); // clear the post array

    if ($selectedCat !== "all") {

// retrieves ALL the video data from the database
      if (!($ret = $connect->prepare("SELECT id, name, category, length, rented FROM inventory WHERE category = ?"))) {
        echo "Insert Preparation Error";
      }

       if (!($ret->bind_param("s", $selectedCat))) {
        echo "Binding Parameters Error";
      }
    }
    else {
      if (!($ret = $connect->prepare("SELECT id, name, category, length, rented FROM inventory"))) {
        echo "Insert Preparation Error";
      }
    }
// if the user wants a specific category, the program will make a SQL query with the category
// if the user wants to view ALL movies, the program will make a SQL query to return all movies
// this if-else loop above controls which prepare call is made

    if (!($ret->execute())) {
      echo "Execute Error";
    } 

    if (!($ret->bind_result($vId, $vidN, $vidC, $vidL, $vidR))) {
      echo "Error retrieving results";
    }

// fetches the results from the database 
    while ($ret->fetch()) {

    $vidN = strtolower($vidN);
    $vidN = ucwords($vidN);
    $vidC = ucwords($vidC);

    if ($vidR == 1) {
      $vidR = "Available";
    }
    else {
      $vidR = "Checked Out";
    }
    // capitalizes first letter with the rest lowercase to make output look nicer

      echo "<tr><td>$vidN<td>$vidC<td>$vidL<td>$vidR<td>";

      echo "<form method=\"POST\" action=\"videos.php\"><input type=\"hidden\" name=\"check\" value=\"$vId\"><input type=\"submit\" value=\"Check In/Out\"></form><td>";
// code above creates the button to check in/out movies
      echo "<form method=\"POST\" action=\"videos.php\"><input type=\"hidden\" name=\"id\" value=\"$vId\"><input type=\"submit\" name=\"deleteOne\" value=\"Delete\"></form></td></tr>";
// code above creates the button to delete movies

    }
    
    echo "</table>";
    $ret->close();
// the code here should fetch the rows - fetching all rows for now to make sure code works
  }
}
?>
  </body>
</html>
