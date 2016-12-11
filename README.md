Simple ORM
=========

Simple ORM is an object-relational mapper for PHP & MySQL (using mysqli). It
provides a simple way to create, retrieve, update & delete records.

This is not intended for large scale projects as it has extra database interaction than necessary. I would suggest [Doctrine](http://www.doctrine-project.org/) for such things.

Simple ORM is a few years old but it has been upgraded over time, therefore **PHP 5.3** is a requirement.


Configuration
--------------

To utilise this tool you need to do a few things:

1. Include the class file.
2. Create a `mysqli` object.
3. Tell `SimpleOrm` to use the `mysqli` connection.

For example:

    // Include the Simple ORM class
    include 'SimpleOrm.class.php';
    
    // Connect to the database using mysqli
    $conn = new mysqli('host', 'user', 'password');
    
    if ($conn->connect_error)
      die(sprintf('Unable to connect to the database. %s', $conn->connect_error));
    
    // Tell Simple ORM to use the connection you just created.
    SimpleOrm::useConnection($conn, 'database');


Object/Table Definition
------------------------

Define an object that relates to a table.

    class Blog extends SimpleOrm { }


Basic Usage
------------

Create an entry:

    $entry = new Blog;
    $entry->title = 'Hello';
    $entry->body = 'World!';
    $entry->save();

Retrieve a record by it's primary key:

    $entry = Blog::retrieveByPK(1);
    
Retrieve a record using a column name:

    $entry = Blog::retrieveByTitle('Hello', SimpleOrm::FETCH_ONE);
    
Update a record:

    $entry->body = 'Mars!';
    $entry->save();
    
Delete the record:

    $entry->delete();
    

Class Configuration
====================

This section will detail how you define your objects and how they relate to your MySQL tables.

A Basic Object
---------------
    class Foo extends SimpleOrm {}


Class Naming
-------------
The following assumptions will be made for all objects:

1. The database used is the database loaded in the `mysqli` object.
2. The table name is the class name in lower case.
3. The primary key is `id`.


Customisation
--------------
You can customise the assumptions listed above using the following static properties:

* database
* table
* pk

For example:

    class Foo extends SimpleOrm
    {
        protected static
          $database = 'test',
          $table = 'foobar',
          $pk = 'fooid';
    }


Data Manipulation
==================

This section will detail how you modify your records/objects.


Creating/Inserting New Records
-------------------------------
You can start a new instance & save the object or you can feed it an array.

    $foo = new Foo;
    $foo->title = 'hi!';
    $foo->save();

or

    $foo = new Foo(array('title'=>'hi!'));
    $foo->save();


Updating
---------
Simply modify any property on the object & use the save() method.

    $foo->title = 'hi!';
    $foo->save();

If you want to have some more control over manipulating data you can use set(), get() & isModified().

    $foo->set('title', 'hi!');
    $foo->save();


Deleting
---------
Use the delete() method.

    $foo->delete();


Data Retrieval
===============

This section will detail how you fetch data from mysql and boot your objects.


Using the Primary Key
----------------------
    $foo = Foo::retrieveByPK(1);

or

    $foo = new Foo(1);


Using a Column Name
--------------------
    $foo = Foo::retrieveByField('bar', SimpleOrm::FETCH_ONE);

By default, the retrieveBy* method will return an array of objects (SimpleOrm::FETCH_MANY).


Select All
-----------
    $foo = Foo::all();


Fetch Constants
----------------
`SimpleOrm::FETCH_ONE` will return a single object or null if the record is not found.

`SimpleOrm::FETCH_MANY` will always return an array of hydrated objects.


Populating from an Array (Hydration)
-------------------------------------
You can pass in an associative array to populate an object. This saves retrieving a record each time from the database.

    $foo = Foo::hydrate($array);


SQL Statements
--------------
Any SQL statement can be used as long as all the returning data is for the object.

Example

    $foo = Foo::sql("SELECT * FROM :table WHERE foo = 'bar'");

This will return an array of hydrated Foo objects.

If you only want a single entry to be returned you can request this.

    $foo = Foo::sql("SELECT * FROM :table WHERE foo = 'bar'", SimpleOrm::FETCH_ONE);


SQL Tokens
-----------
The table name & primary key have shortcuts to save you writing the same names over & over in your SQL statements:

* `:database` will be replaced with the database name.
* `:table` will be replaced with the table name.
* `:pk` will be replaced with the primary key field name.


Extra Data & Aggregate Functions
---------------------------------
Any extra data that does not belong to object being loaded will have those fields populated in the object:

    $foo = Foo::sql("SELECT *, 'hi' AS otherData FROM :table WHERE foo = 'bar'", SimpleOrm::FETCH_ONE);

    echo $foo->otherData; // returns 'hi'

This can be useful if you plan to use aggregate functions within your object & you want to pre-load the data.


Filters
========

Input & output filters can be created to alter data going into the database & when it comes out.

To add a filter, you only need to create a method with either filterIn or filterOut as a prefix (e.g. filterIn, filterInHi, filterIn_hi).

These methods will automatically fire when data is loaded into the object (hydration) or saved into the database (save, update, insert).


Input Filters
--------------

Data being saved to the database can be modified.

For example:

    class Foo extends SimpleOrm
    {
        protected function filterIn_dates ($data)
        {
            $data['updated_at'] = time();

            return $data;
        }
    }

In the example above, every time the object is saved, the updated_at field is populated with a current time & date.

Note: You must return the input array otherwise no fields will be updated.


Output Filters
---------------

Any data loaded into the object will be passed through any output filters.

    class Foo extends SimpleOrm
    {
        protected function filterOut ()
        {
            $this->foo = unserialize($this->foo);
        }
    }

In the example above, each time the object is hydrated, the foo property is unserialized.
