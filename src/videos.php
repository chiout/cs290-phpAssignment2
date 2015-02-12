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
$name=$_POST['name'];
$category=$_POST['category'];
$length=$_POST['length'];

if (empty($_POST['name']) || empty($_POST['category']) || empty($_POST['length'])) {
  echo "Please fill in all required fields.";
  $_POST = array();
  die();
}
 
if (ctype_punct($name) || ctype_punct($category)) {
  echo "You have entered invalid characters. Please try again.";
  die();
}
// This makes sure that the name and category values are not completely made of punctuation symbols, and that category is completely made of letters.

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

?>

    </p>

  </body>
</html>
