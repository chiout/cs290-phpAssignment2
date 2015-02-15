<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('cred.php');

$connect = new mysqli($host, $user, $pd, $db);
if ($connect->connect_errno) {
  echo "Connection to database failed. \n";
  echo $connect->connect_errno.": ".$connect->connect_error;
}
/*
this code above connects to the database, connection code is based on lines 1-6 in Example #1 on us2.php.net/manual/en/mysqli.quickstart.prepared-statements.php
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
      </form><!--html form to take in input to add a video-->
<?php
$flag1 = false; 
// set to false initially to prevent a video entry from being added to the database without checking input first

if (isset($_POST['name']) || isset($_POST['category']) || isset($_POST['length'])) {
  if (empty($_POST['name'])) { // makes sure name is not an empty string
    echo "Name is a required field.";
    $_POST = array(); // empty the POST array if name is not filled out
  }
  else {
    if (!empty($_POST['length'])) { // if length is not an empty string, then its value is checked

      if(!ctype_digit($_POST['length'])) {
        echo "Length must be a positive numerical value";
        $_POST = array();
      } // ctype will catch negative values as "-" is not a number
      else {
        if ($_POST['length'] < 0) {
          echo "Length must be a value greater than 0.";
          $_POST = array();
        } // this code may not be necessary but kept as a second line of defense
          // also makes sure length > 0
        else {
          $flag1 = true;
// if the length > 0 and name is filled out, then $flag1 is set to true
        }
      }    
    }
    else {
      $flag1 = true; 
// if name is filled out and length is an empty string, then $flag1 is set to true
    }
  }

}
// $flag1 needs to be true in order for the program to allow a video entry to be added
if ($flag1 === true) {
  $name=$_POST['name'];

  if (empty($_POST['category'])) {
    $category = "Not Available";
  }
  else {
    $category=$_POST['category'];
  }

  if (empty($_POST['length'])) {
    $length = NULL;
  }
  else {
    $length=$_POST['length'];
  }
// if no input is given for category or length fields, they are set to "Not Available" and NULL respectively

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
  $flag1 = false; // set back to false so this if block cannot be executed again until $flag1 is true
}
/*
this code above is based on lines 14-27 and 36-37 in Example #3 on us2.php.net/manual/en/mysqli.quickstart.prepared-statements.php

it uses prepared statements to send SQL queries to the database in order to add data to the database
prepared statements are also used later to make more calls to the database
*/

?>
    </p>
    <p>
      <h3>Delete All Videos</h3>
      <form method="POST" action="videos.php">
	<input type="hidden" name="deleteAll" value="Delete All">
        <input type="submit" value="Delete All">
      </form> 
<!-- Used hidden input type to submit the "Delete All" value via a POST request when the Delete All button is pressed; got the idea for this from aleation's post and first line of code on http://stackoverflow.com/questions/13515496/how-to-get-id-of-submit-type-button-when-button-is-pressed-php-html
This method is also used later for the check in/out and delete buttons for each video entry.
-->
    </p>
<?php
if (isset($_POST['deleteAll']) && $_POST['deleteAll']== "Delete All" ) {
 
  if (!($del = $connect->prepare("DELETE FROM inventory"))) {
    echo "Insert Preparation Error";
  }
 
  if (!($del->execute())) {
    echo "Execute Error";
  }
// this deletes ALL the videos from the database if the Delete All value is received from the hidden input

  $del->close();
  $_POST = array(); // clear the array just in case
}
/* deletes ALL videos */


if (!($count = $connect->prepare("SELECT COUNT(*) FROM inventory"))) {
  echo "Insert Preparation Error";
} // make a SQL call to find out how many entries are in the table
/*
Took the "SELECT COUNT(*) FROM" code from http://dev.mysql.com/doc/refman/5.1/en/counting-rows.html, first line of code on the page
*/ 

if (!($count->execute())) {
  echo "Execute Error";
}

if (!($count->bind_result($number))) {
  echo "Error retrieving results";
}

$count->fetch();
// this code above will analyze how many entries are in the table
// the number of entries will affect the html output of the page
/*
this code above is based on lines 24-31 in Example #6 on us2.php.net/manual/en/mysqli.quickstart.prepared-statements.php
bind_result and fetch are also used later to retrieve more data from the database
*/

$count->close();

echo "<h3>Current Movies in Inventory</h3>";
echo "<p>Changes made here by clicking the buttons are immediate; however please use the drop down menu and/or press \"Filter\" to see the changes.</p>";

if ($number > 0) {
// if there are row entries in the database then those entries will be called for via different events
// the events are triggered via a category drop down menu which is coded for below
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

    if ($vidCat !== "Not Available") {
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
  }
// all unique category values are taken into the $categories array first
// if the category value is already in the array, it is not added to the array
// if the value = "Not Available", it is not added to the array

  if (count($categories) !== 0) {
    foreach ($categories as $value) {
      echo "<option value=\"$value\">$value</option>";
    } // prints out each $categories value to the drop down menu
// set its value equal to its name so that the value can be used in later SQL queries
echo "<option value=\"all\">All Movies</option></select>";
// add an "All Movies" option with a value of "all"
// if selected, this will prompt SQL queries to return all rows from the database
echo "<input type=\"submit\" value=\"Filter\"></form>";
  }
  else {
    echo "Array Error"; // this should never execute, but put here just in case
  }

  $cat->close();
// the above code takes in category input

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
// this deletes the video based on its id (Primary key in database)
// it will delete the row entry with a particular id 
// this code above is executed when the delete button for a particular video entry is clicked on

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
// this code above is executed when the check in/out button is clicked on for a video entry of a particular id
// this code checks to see what the value of rented is - 0 or 1 (false or true)
// then it will change the value accordingly

    if ($status == 1) {
      if (!($rent = $connect->prepare("UPDATE inventory SET rented = 0 WHERE id=(?)"))) {
        echo "Insert Preparation Error";
      }
    } // if rented was 1, then it is updated to be 0
    else {
      if (!($rent = $connect->prepare("UPDATE inventory SET rented = 1 WHERE id=(?)"))) {
        echo "Insert Preparation Error";
      } 
    } // if rented was 0, then it is updated to be 1

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
// if a category is selected to display videos, the code below executes
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

    if ($vidL == NULL) {
      $vidL = "N/A";
    }
// if the length is NULL, then "N/A" will be printed out

      echo "<tr><td>$vidN<td>$vidC<td>$vidL<td>$vidR<td>";

      echo "<form method=\"POST\" action=\"videos.php\"><input type=\"hidden\" name=\"check\" value=\"$vId\"><input type=\"submit\" value=\"Check In/Out\"></form><td>";
// code above creates the button to check in/out movies
// if the check in/out button is pressed, it will send a POST request
// this will prompt a SQL query to be sent to change the value of rented from 1 to 0 or 0 to 1 in the database
// the value sent via POST is the id of the video in the database
      echo "<form method=\"POST\" action=\"videos.php\"><input type=\"hidden\" name=\"id\" value=\"$vId\"><input type=\"submit\" name=\"deleteOne\" value=\"Delete\"></form></td></tr>";
// code above creates the button to delete movies
// the value sent via POST is the id of the video in the database
// this will prompt a SQL query to delete the row entry which has that id

    }
    
    echo "</table>";
    $ret->close();
// the code above prints out all the row entries from the database
  }
}
else {
// if there are 0 entries in the database then this code will execute
  echo "<form><select>";
  echo "<option></option>";  
  echo "</select><input type=\"submit\" value=\"Filter\"></form>";

  echo "<table cellpadding=5px>";
  echo "<tr><th>Name<th>Category<th>Length (min)<th>Status<th>Check In/Out<th>Delete</th></tr>";
  echo "</table>";
// if there are no movies to display then a blank drop down menu and table will show
}
?>
  </body>
</html>
