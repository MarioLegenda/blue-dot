###BlueDot###
*Pure sql database abstraction layer*

###Content###

1. Introduction
2. Installation
3. The basics
    * Initial configuration
    * Database connection
4. Terminology
5. Simple statements
    * Basic example
    * Parameters explained
    * Working with models
6. Scenario statements
    * Basic example
    * Parameters explained
    * 'use' configuration feature
    * 'foreign_key' configuration feature
    * 'if_exists' and 'if_not_exists' configuration feature
    * A complex example
7. Callable statements
8. Statement builder
9. Promise interface
10. Configuration reference

###1. Introduction###

**BlueDot** is a database abstraction layer that works with pure sql but returns 
domain objects that you can work with. It's configuration based and 
requires minimal work and setup to start working with it. The reason I 
created this tool is simple free time. Hope someone will find it useful.

This documentation is written in a way in which you will first learn how
to execute sql queries but getting the result and manipulating it is
covered in *Chapter 9: Promise interface*

###2. Installation###

**BlueDot** requires PHP 7.0 or higher

Install it with [composer](https://getcomposer.org/)

    composer require mario-legenda/blue-dot 1.0.0
    
###3. The basics###

**3.1 Initial configuration**

**BlueDot** is fully configurable trough .yml configuration file that you specify in the ```BlueDot``` constructor. 

    use BlueDot\BlueDot;
    
    $blueDot = new BlueDot('/path/to/config/file/configuration.yml');
    
You can also instantiate via singleton

    use BlueDot/BlueDot
    
    $blueDot = BlueDot::instance('/path/to/config/file/configuration.yml');
    
**3.2. Database connection**

    configuration:
        connection:
            host: localhost
            database_name: world
            user: root
            password: root
            
And you are all set to make your first query to the database. You can also establish a 
connection with a Connection object which you can pass as the second argument to 
**BlueDot** constructor. 

    $connection = new BlueDot\Database\Connection(array(
        'host' => '127.0.0.1',
        'database_name' => 'database',
        'user' => 'root',
        'password' => 'root',
    ));
    
    $blueDot = new BlueDot\BlueDot('/path/to/configuration.yml', $connection);
    
You can also instantiate **BlueDot** without configuration and only a **Connection** object
but you could not execute any sql that you configured in your config .yml file.
You can, however, execute sql statements with the **statement builder**. More on 
statement builder later on.

Also, database setup in your .yml configuration is not mandatory. You can set
the connection with **BlueDot::setConnection()** method that accepts a 
**BlueDot\Database\Connection** object.

The **Connection** object also has methods to set dsn values, like 
**Connection::setDatabaseName()**, **Connection::setHost()** etc... Also, there is a **Connection::setAttribute()**
method with which you can set a PDO attribute for establishing a connection.

    $connection->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
    
*NOTE: Errormode attribute is already set, together with persistent connection
and utf8 charset*

###4. Terminology

In the following text, I refer to *statements*. A statement is a configuration value
that holds the configuration for an sql query to be executed.

For example...

    simple:
        select:
             find_users:
                 sql: 'SELECT * FROM users'
      
*simple.select.find_users* is a statement, whereas 'SELECT * FROM users' is an
sql query. So, when I mention the word *statement*, I mean *simple.select.find_users*,
but when I mention an sql query, I mean 'SELECT ...', actual sql query.

In **BlueDot**, there are 3 types of statement:
- simple
- scenario
- callable

Therefor, when I say statement, I mean one of those three.

### Simple statements

**3.1 Basic example**

Simple statement is a single sql query defined in configuration and executed in code.

For example...

    configuration:
        connection:
            host: localhost
            database_name: world
            user: root
            password: root
            
        simple:
            select:
                find_users:
                    sql: "SELECT * FROM users"
                    
*NOTE: from now on, I will not include connection parameters*
                
In your code, instantiate **BlueDot** and run the ```BlueDot::execute()``` method with notation ```simple.select.find_users```

    $blueDot->execute('simple.select.find_users');
    
This line of code will execute the sql query for statement ```simple.select.find_users```.
You will see how to get the actual result of this statement lateron.

There are 4 type of simple statements:
- select
- insert
- update
- delete

To expand to the former example, an update simple statement would look like this:

        simple:
            select:
                find_users:
                    sql: "SELECT * FROM users"
                    
            update:
                update_user:
                    sql: "UPDATE users SET name = 'Mary' WHERE id = 6"
                    
*delete* and *insert* statements are defined the same way and you execute them the same way.

Now, the result. The product of *BlueDot::execute()* method is a *promise*.
A promise can be a **success** or a **failure**. If the query returned an empty
result, the statement is a failure. If it return some results, then it is a success.

For now, I'm only going to show you the basics of *Promise* interface. There is a 
dedicated chapter only on promises.

    $blueDot->execute('simple.select.find_users')
        ->success(function(PromiseInterface $promise) {
            echo 'Statement returned a result';
        })
        ->failure(function(PromiseInterface $promise) {
            echo 'Statement failed because it did not return any result';
        });

If the statement *simple.select.find_users* returned a result, *success* functions
will be executed. If it did not, *failure* function will be executed.
 














