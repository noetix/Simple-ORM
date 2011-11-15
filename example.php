<?php

// Load parameters.
$params = parse_ini_file(sprintf('%s/parameters.ini', __DIR__), true);

// Include the SimpleOrm class
include 'SimpleOrm.class.php';

// Connect to the database using mysqli
$conn = new mysqli($params['database']['host'], $params['database']['user'], $params['database']['password']);

if ($conn->connect_error)
  die(sprintf('Unable to connect to the database. %s', $conn->connect_error));

// Tell SimpleOrm to use the connection you just created.
SimpleOrm::useConnection($conn, $params['database']['name']);

// Define an object that relates to a table.
class Blog extends SimpleOrm { }

// Create an entry.
$entry = new Blog;
$entry->title = 'Hello';
$entry->body = 'World!';
$entry->save();

// Use the object.
printf("%s\n", $entry->title); // prints 'Hello';

// Dump all the fields in the object.
print_r($entry->get());

// Retrieve a record from the table.
$entry = Blog::retrieveByPK($entry->id()); // by primary key

// Retrieve a record from the table using another column.
$entry = Blog::retrieveByTitle('Hello', SimpleOrm::FETCH_ONE); // by field (subject = hello)

// Update the object.
$entry->body = 'Mars!';
$entry->save();

// Delete the record from the table.
$entry->delete();

/*

vm1:/home/alex.joyce/SimpleOrm# php example.php 
Hello
Array
(
    [id] => 1
    [title] => Hello
    [body] => World!
)
vm1:/home/alex.joyce/SimpleOrm# php example.php 
Hello
Array
(
    [id] => 2
    [title] => Hello
    [body] => World!
)

*/
