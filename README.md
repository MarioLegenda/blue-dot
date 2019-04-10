## BlueDot
*Pure sql database abstraction layer*

Note:

This project is still in development and I have added more features that are not in this documentation. Version 2.0 is on the way, and when its finished, I will update the documentation

## Content

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
    * 'use' configuration option
    * 'foreign_key' configuration option
    * 'if_exists' and 'if_not_exists' configuration option
7. Service statements
8. Prepared execution
9. Repositories
10. Statement builder
11. Promise interface and getting the result
    * Introduction
    * PromiseInterface
    * Entity result object
12. Filters
    * A list of possibilities
    * Using configuration filters
13. Imports
14. Conclusion
15. Setting up tests

## 1. Introduction

**BlueDot** is a MySQL database abstraction layer that works with plain sql but returns 
domain objects that you can work with. It's configuration based and 
requires minimal work and setup to start working with it. The reason I 
created this tool is simple free time and the need for a better tool to handle
plain SQL. Hope someone will find it useful.

You can use this tool in a small to mid level project but I would not recommend using
it for enterprise projects. I used it in a mid level project and only on certain places
and it worked very well. Don't use it as the only tool to work with MySQL but as a supplement
to Doctrine or Propel in instances where you need fast database lookup.

You can also use it in database intensive migrations where you have tens of millions 
rows of data and you need to change it as fast as possible with minimal memory 
consumption. Since BlueDot is using plain SQL and does not track object or previously
executed queries like Doctrine or Propel, it is a fast alternative to use it alongside
Phinx, for example.

You can also use it in simple CRUD operations on applications like blogs or in instances
where you work with large quantities of data.

This documentation is written in a way in which you will first learn how
to execute sql queries but getting the result and manipulating it is
covered in *Chapter 12: Promise interface and getting the result*

## 2. Installation

**BlueDot** requires PHP 7.0 or higher

Install it with [composer](https://getcomposer.org/)

    composer require mario-legenda/blue-dot
    
Current version of BlueDot is 2.0.4.
    
## 3. The basics

#### 3.1 Creating BlueDot and the initial configuration

BlueDot works by parsing the configuration .yml file and executing it later on, but
you can instantiate it without the file. This is because you can use BlueDot only 
for the **StatementBuilder** (we will talk about the StatementBuilder later). You can
also inject configuration file and the connection to MySql later. This makes BlueDot
light and configurable.

    use BlueDot\BlueDot;
    
    $blueDot = new BlueDot();
    
    // OR
    
    $blueDot = new BlueDot('/path/to/file.yml');
    
Not that the .yml configuration has to be a absolute path to that file.
    
If you wish to inject the configuration later on, you can do that with the **BlueDot::setConfiguration()**
method.

    // the path to the file must be an absolute path
    $blueDot->setConfiguration('/path/to/file.yml');
    
You cannot call **BlueDot::setConfiguration()** more than once. BlueDot works on a concept
of repositories where every file is a repository and you can switch between repositories as you wish,
but you cannot load an existing repository (an already loaded .yml file). More on repositories later on.

#### 3.2. Database connection

This is how you setup the connection information.

    configuration:
        connection:
            host: localhost
            database_name: world
            user: user
            password: password

Also, database setup in your .yml configuration is not mandatory. You can set
the connection with **BlueDot::setConnection()** method that accepts a 
**BlueDot\Database\Connection** object.

    use BlueDot\Kernel\Connection\ConnectionFactory;
    use BlueDot\Kernel\Connection\Connection;
    use BlueDot\BlueDot;
    
    $blueDot = new BlueDot();
    
    /** @param Connection $connection */
    $connection = ConnectionFactory::createConnection([
        'host': 'localhost',
        'database_name': 'world',
        'user': 'user',
        'password': 'password'
    ]);
    
    $blueDot->setConnection($connection);

The **Connection** object also has methods to set dsn values, like 
**Connection::setDatabaseName()**, **Connection::setHost()** etc... Also, there is a **Connection::setAttribute()**
method with which you can set a PDO attribute for establishing a connection.

    $connection->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
    
*NOTE: Errormode attribute is already set, together with persistent connection
and utf8 charset*

If you need to close the connection to the database, use *Connection::close()* method.

    $blueDot->getConnection()->close();

It is also important to note that the actual connection to MySql is not established when you
create the instance of the Connection object, but when BlueDot executes **Connection::connect()**.
That method is executed only in the moment that BlueDot knows that every check and validation
went successfully.

If you already have an established **PDO** object, after creating the Connection object
from BlueDot, you can set your own **PDO** object trough **Connection::setPdo()** method. 

## 4. Terminology

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
- service

Therefor, when I say statement, I mean one of those three.

## 5. Simple statements

#### 5.1 Basic example

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
You will see how to get the actual result of this statement later on.

There are 4 type of simple statements:
- select
- insert
- update
- delete

To expand on the former example, an update simple statement would look like this:

        simple:
            select:
                find_users:
                    sql: "SELECT * FROM users"
                    
            update:
                update_user:
                    sql: "UPDATE users SET name = 'Mary' WHERE id = 6"
                    
*delete* and *insert* statements are defined the same way and you execute them the same way.

Now, the result. The product of *BlueDot::execute()* method is a *promise*.
It has nothing to do with promises in Javascript. It is just a wrapper around the
result that I called a promise. Nothing special. More on promises and the result
of the query later on. There is a dedicated chapter for it.

#### 5.2 Parameters explained

PHP PDO can bind parameters with *PDO::prepare()*. **BlueDot** supports this 
feature in a slightly different way.

*NOTE: If you provide parameters in configuration but not in code, and vice versa
an exception will be thrown*

To bind a parameter to a statement, you need to provide that parameter in configuration
and in code. Depending on the nature and number of parameters supplied in
code, **BlueDot** decides weather to execute the statement only once
or multiple times. 

Take a look at this statement
    
    simple:
        select:
            find_user:
                sql: "SELECT * FROM users WHERE id = :id"
                parameters: [id]
                    
    $blueDot->execute('simple.select.find_users', array(
        'id': 6,
    ));
    
This statement is executed only once and a user is returned whose 
*id* is 6.

But what if you need to execute a single sql query multiple times
with different parameters?

    simple:
        insert:
            create_users:
                sql: "INSERT INTO users (name) VALUES (:name)"
                parameters: [name]
                
If you provide the same parameter as in previous example, this statement
will be executed only once.

    $blueDot->execute('simple.insert.create_users', array(
        'name': 'Peter',
    ));
    
But if you provide multiple parameters as multiple arrays, then this statement
will execute as many times as there are parameters.

    $blueDot->execute('simple.insert.create_users', array(
        array('name' => 'Mary'),
        array('name' => 'Jean'),
        array('name' => 'Zoey'),
        array('name' => 'Jennifer'),
    ));
    
*simple.insert.create_users* will execute 4 times because there are 4 parameters
supplied to the *execute* method.

There is a shorthand way of executing multiple statements but only when there is
a single parameter to be bound in the statements sql query. In our 
*simple.insert.create_users* statement, only a single *name* parameter has
to be bound, so you can use that shorthand.

    $blueDot->execute('simple.insert.create_users', array(
        'name' => array(
            'Mary',
            'Jean',
            'Zoey',
            'Jennifer',
        ),
    ));
    
This shorthand way **only** works if there is only one parameter to be bound
to an sql query. If sql query has to be bound with multiple parameters, this
way won't work and you will receive an exception. For example, if the above
sql query has to be bound with a *name* parameter and an *id* parameter.

To conclude, a statement is executed as many times as there are parameters
for that statement. If you provide multiple parameters, the statement will execute
as many times as there are parameters. If you provide only one parameter,
statement will execute only once.

#### 5.3 Working with models

Database tools like Doctrine use models to make communication with the
database easier and more descriptive. Simple statements also provide that
feature.

For example, let's say we have a *language* table with columns *id* and 
*language*. Our model would look like this...

    namespace App\Model;
    
    class Language 
    {
        private $id;
        
        private $language;
        
        public function setId($id) : Language
        {
            $this->id = $id;
            
            return $this;
        }
        
        public function getId() 
        {
            return $this->id;
        }
        
        public function setLanguage($language) : Language
        {
            $this->language = $language;
            
            return $this;
        }
        
        public function getLanguage() 
        {
            return $this->language;
        }
    }
    
Following previous examples, we can create a new language by using this model:

    simple:
        insert:
            create_language:
                sql: "INSERT INTO languages (name) VALUES (:language)"
                parameters: [language]
                    
    $language = new Language();
    $langauge->setLanguage('french');
    
    $blueDot->execute('simple.insert.create_language', $language);
    
**BlueDot** concludes from configuration that you want the *language* parameter to 
be bound to the statement sql query. It then concludes that you supplied an object
as a parameter and looks for a *Language::getLanguage()* method on that object.
If it finds one, it binds the value returned from that method to the *language*
parameter of the sql query.

It is important to say that there has to be a *get* method on the model for the parameter(s)
that you want to bind. For example, if you also need to bind a *name* parameter,
there has to be a *Language::getName()* parameter on the *Language* model.

Model binding is a two-way process and it can be used to fetch models from the database.
For example, to expand on our *users* example, you could have a *User* with fields *id*, *name*, *username* and *password*.
You would like to return an array of populated users from the database:
 
    simple:
        select:
            find_users:
                sql: "SELECT * FROM users"
                model:
                    object: App\Model\User
                    
    $blueDot->execute('simple.select.find_user');
    
**BlueDot** will return an array of *User* objects populated with the value for
*id*, *name*, *username* and *password*.

You can combine these two approaches to find a specific user...

    simple:
        select:
            find_user:
                sql: "SELECT * FROM users WHERE id = :id"
                parameters: [id]
                model:
                    object: App\Model\User
                    
    $userId = 6;
    $user = new User();
    $user->setId($userId);
    
    $blueDot->execute('simple.select.find_user', $user);
    
**BlueDot** will bind the return value of method *User::getId()* to the *id*
parameter and return a new *User* object populated will all the returned values.

**BlueDot** works with column names. If you have a *last_name* column name and an object
is supplied as a parameter, **BlueDot** will search for a method *User::getLastName()/User::setLastName()*.
You can also name your table columns *lastName* and model binding will work. **BlueDot**
will not bind returned column values to an object if the object does not have a corresponding
*get* and *set* method for that column. For example, if a table contains a column *date_created*
but the model does not have a *Model::setDateCreated()*, it will not bind that columns value
to the supplied model.

If you have a column name that is different than the model property, you can use *properties*
configuration.

    simple:
        select:
            find_user:
               sql: "SELECT * FROM users WHERE id = :id"
               parameters: [id]
               model:
                   object: App\Model\User
                   properties: { find_user.created_on: dateCreated }
                    
 In this example, *User* object has a property *dateCreated* with its corresponding *set*
 and *get* methods but column name is *created_on*. **BlueDot** will search for a 
 *User::setDateCreated()* method and save the value from *created_on* column. If
 **BlueDot** could not find the property on the model or in *properties*, then it 
 will skip that column and will not put it in the model. For example, if the table
 contains a column *updated_on* but the model does not have a method *setUpdatedOn()* and
 you haven't supplied a replacement in the *properties* configuration, that column will
 be skipped.
 
 
 **IMPORTANT**
 
 *model* configuration property is used for telling **BlueDot** to bind return column values
 to that model. You don't have to put that configuration if you provide a model as 
 a parameter. *model* configuration property is only used for returning models.
 
## 6. Scenario statements
 
 #### 6.1 Basic example
 
 Scenario statements are a group of statements that are executed together,
 in an atomic way. That means, if one of those statements failed, none of the
 statements will be executed. They could describe a search feature on an 
 application or a calculating feature that requires a lot of database traffic and
 different information stored in many tables.
 
 Let's create a basic example from the real world. In a user registration scenario,
 you would first, search for a user with a registration username/email and then
 create a the new user.
 
     scenario:
         create_user:
             atomic: true
             statements:
                 find_user_by_username:
                     sql: "SELECT * FROM users WHERE username = :username"
                     parameters: [username]
                 create_user:
                     sql: "INSERT INTO users (name, username, password) VALUES (:name, :username, :password)"
                     parameters: [name, username, password]
                     if_exists: find_user_by_username
                 
     $blueDot->execute('scenario.create_user', array(
         'find_user_by_username' => array(
             'username' => 'John',
         ),
         'create_user => array(
             'name' => 'Jennifer',
             'username' => 'jennifer@gmail.com',
             'password' => 'someweakpassword',
         ),
     ));
     
There are a couple of things to say about this simple example.

The name of this scenario is *create_user*. *find_user_by_username* and
*create_user* are it's statements. Statements are executing in the order in which
they appear in configuration with an exception of *use*, *foreign_key* and
*if_exists/if_not_exists* options. Those options are executed before the statement
in which those options are.

Let me explain. *create_user* statement has an *if_exists* option. **BlueDot**
starts executing statements in the order in which they appear in configuration.
First, it executes *find_user_by_username*. Then, it goes to execute *create_user*.
It sees that *create_user* has *if_exists* option with the name of the statement for
which existance it has to check. It then check if the *if_exists* statement is already
executed. If it is not, it executes it and after that, executes *create_user*.

In our example, when **BlueDot** wants to execute *create_user*, it sees that 
*if_exists* statement is already executed, skips it's execution and executes *create_user*.

You may have noticed the *atomic* option. *atomic* option tells **BlueDot** that all
the statements are executed in transaction. That means that if one statement fails, 
the entire scenario *rolls back* and none of the statements affect the database. If you
set this option to **false**, the statements will be executed one by one and if one fails,
other would affect the database.

This is a basic example of what scenarios can do. In this example, I introduced
*if_exists* option. *if_exists/if_not_exists* options check if the statement under
those options exists or doesn't exist. Depending on that condition, statement that
has those options will or will not be executed. More about scenario options later
in this chapter.

#### 6.2 Parameters explained
 
Parameters for scenarios are similar to simple statements in most way but with some 
differences. You have to provide the name of the scenario statement as an array key,
and an the parameters array as its value.

In the previous example

     $blueDot->execute('scenario.create_user', array(
         'find_user_by_username' => array(
             'username' => 'John',
         ),
         'create_user => array(
             'name' => 'Jennifer',
             'username' => 'jennifer@gmail.com',
             'password' => 'someweakpassword',
         ),
     ));
     
you have two statements, *find_user_by_username* and *create_user*. You provide
parameters for those statements by naming them as keys with parameter arrays. 
The rules for parameters are the same as for simple statements with two exceptions:
you cannot provide a model as a parameter and you can assign **null** as a parameter.

If you assign **null** as a parameter for a scenario statement, that statement will
not execute. This is useful if, for example, you have a delete or an update query that you do not
want to execute in some cases, but in others you do.

### 6.3 'use' configuration option

*use* option is a powerful scenario feature. With it, you can bind a parameter with
the return value of another statement.

For example, if a blog could be saved in many languages (locals), during saving, you
would have to check if the language in which you are saving exists and then save
the text of the blog.

    scenario:
        save_blog:
            atomic: true
            statements:
                find_language:
                    sql: "SELECT id FROM locales WHERE locale = :locale"
                    parameters: [locale]
                save_block:
                    sql: "INSERT INTO blogs (locale_id, blog_text) VALUES(:locale_id, blog_text)"
                    if_exists: find_language
                    use:
                        statement_name: find_language
                        values: { find_language.id: locale_id }
                
    $blueDot->execute('scenario.save_blog', array(
        'find_language' => array(
            'locale' => 'en',
        ),
        'save_block' => array(
            'blog_text' => 'Some cool blog text',
        ),
    ));
   
**BlueDot** executes statements in the order in which they appear in configuration. First, he executes
*find_language*. Next on the menu is *save_block*. The first thing **BlueDot** sees is 
*if_exists*. This option tells **BlueDot** that *save_block* should be executed only
if *find_language* statement returned some results i.e an *en* locale has been found.
If it has been found, it sees that it has a *use* option statement.

A *use* option gives you the opportunity to bind parameters with values returned from other
statements. In the above example, you configured *find_language.id* returned from statement
to be bound to *save_block.locale_id*  parameter. So, *find_language.id* binds to *save_block.locale_id*.

If the *use* statement result is not executed, **BlueDot** executes it and only then it 
executes *save_block* statement.

Although *use* option is a useful feature, it has its restrictions. A statement that is a
*use* option in some other statement can only be a *select* sql query and it has to return
a single row. In the above example, if *find_language* would have returned multiple rows, 
**BlueDot** would have thrown an exception.

The order in which you put the *use* option statement does not matter. In the above example,
*find_language* could be configured below *save_block*. In that case, *save_block* would see 
that it has a *use find_language* statement and that statement bould be executed. By the 
time execution gets to execute *find_language*, it would already be executed and it would be skipped.

#### 6.4 'foreign_key' configuration option

A *foreign_key* option is an option to use when you wish to bind a parameter of a certain
statement to a *last_insert_id* of an insert statement. It is best to see it in an example.

    scenario:
        create_word:
            atomic: true
            statements:
                create_word:
                    sql: "INSERT INTO words (word) VALUES (:word)"
                    parameters: [word]
                create_translations:
                    sql: "INSERT INTO translations (word_id, translation) VALUES (:word_id, :translation)"
                    parameters: [translation]
                    foreign_key:
                        statement_names: [create_word]
                        bind_them_to: [word_id]
                    
    $blueDot->execute('scenario.create_word', array(
        'create_word' => array(
            'word' => 'some word',
        ),
        'create_translations' => array(
            'translation' => 'some translation of a word',
        ),
    ));

This is a classic **one-to-one** relationship. *translations* table has a field *word_id*
that accepts an *id* of a word with which we want to connect our translations. In a classic
usage of PHP PDO, you would execute *create_word* sql query and call *PDOConnection::lastInsertId()*
method to get the last inserted id of that query. Then, you would execute *create_translations*
sql query and bind that *last_insert_id* to parameter *word_id*.

The above example describes a *one-to-one* relationship but you could easily transform this
relationship to *one-to-many* with the same scenario configuration.

    $blueDot->execute('scenario.create_word', array(
        'create_word' => array(
            'word' => 'some-word',
        ),
        'create_translations' => array(
            'translations' => array('translation 1', 'translation 2', 'translation 3'),
        )
    ));

By changing to parameter type of *create_translations* statement, we have told **BlueDot** to insert
3 statements with translations to the *last_insert_id* of statement *create_word*.

You would notice that *statement_names* and *bind_them_to* are plural. That is because they work
on multiple statements. That means you can bind as many statement you want. Consider the following example:

    normalized_user_insert:
        atomic: true
        statements:
            insert_user:
                sql: "INSERT INTO user (username, name, lastname) VALUES (:username, :name, :lastname)"
                parameters: [username, name, lastname]
            insert_address:
                sql: "INSERT INTO addresses (user_id, address) VALUES (:user_id, :address)"
                parameters: [address]
                foreign_key:
                    statement_names: [insert_user]
                    bind_them_to: [user_id]
            create_reference_user:
                sql: "INSERT INTO reference_user (user_id, address_id) VALUES (:user_id, :address_id)"
                foreign_key:
                    statement_names: [insert_user, insert_address]
                    bind_them_to: [user_id, address_id]
                    
The values of *last_insert_id* from *insert_user* and *insert_address* are bound
to the parameters of *:user_id* and *:address_id* of *create_reference_user*. The order matter.
This is **very** important. First statement result in the array of *statement_names* corresponds 
to the first value of array *bind_them_to*.

This is a simple example, but it could be tedious work if multiple insert statements are 
necessary. With scenarios, this is a trivial task. 

#### 6.5 'if_exists' and 'if_not_exists' configuration option

You have already seen examples of *if_exists* and *if_not_exists* options. These options
tell **BlueDot** to execute or not to execute a statement if some other statement exists.

Both options can be used with any sql query but there is a catch. In Mysql, if an update does
not change any information, **BlueDot** cannot know if a statement is executed or not.
Internally, for *insert*, *update* or *delete* sql queries, it call the *PDOStatement::rowCount()* method to see how many rows have been
affected. If no rows have been affected, that kind of statement will be considered a nonexistent.
Don't forget that when you use those sql queries with these options.

## 7. Service statements

A service statement is an object that extends **BlueDot\Configuration\Flow\Service\BaseService**.
In that object, you will receive **BlueDot** instance and parameters array that you could
use as a dependency injection container. It is best to see it in an example.

    service:
        my_service:
            class: Some\Namespace\MyClass
        
    class MyService extends use BlueDot\Configuration\Flow\Service\BaseService;
    {
        public function run() 
        {
            // fetch some parameter
            
            $someParameter = $this->parameters['some-parameter'];
            
            // execute some simple statement
            
            $this->blueDot->execute('simple.select.some_statement');
            
            // execute some scenario
            
            $this->blueDot->execute('simple.select.some_scenario');
        }
    }
    
    $blueDot->execute('callable.my_callable', array(
        'some-parameter' => new SomeObject(),
        'other-parameter' => 'some string',
        'number-parameter' => 6,
    ));

A service has a *run()* method that **BlueDot** executes. Purpose of a service
is to group many scenarios or simple statements together. The return value of a service
is anything that *run()* method returns but encapsulated in a Promise. More on promises
later.

## 8. Prepared execution

Prepared execution allows you to run multiple statements as one atomic mysql query, atomic
meaning that if one of them fails, none of them will succeed and the result of success queries 
will be rolled back. The can be used to execute queries that are unrelated in the application that you
are building, but in some instances, they can be.

Prepared execution can be used to execute multiple scenario and simple statement in one atomic action
that you would not get if you used scenario or single statement on their own.

You add the statements to be executed later with the *BlueDot::prepareExecution()* method
that has the same signature as the *BlueDot::execute()* method. It accepts the same parameters; the name
of the statement, and the parameters of the query.

You execute all the prepared statements with the method *BlueDot::executePrepared()*

For example:

    $blueDot->prepareExecution('simple.insert.user', [
        'name' => 'Billie',
        'last_name' => 'Holliday',
        'email' => 'billieholliday@bestjazz.com'
    ]);
    
    $blueDot->prepareExecution('simple.insert.blog', [
        'blog' => 'text of the blog',
    ]);
    
    $blueDot->prepareExecution('simple.insert.some_statement_that_has_nothing_to_do_with_user_or_blog');
    
    $blueDot->executePrepared();

## 9. Repositories

Repositories are a way of keeping your queries organised by the logic they are meant to serve.

For example, user specific queries would be in *user.yml* where as blog specific queries would
be in *blog.yml*. It is similar to Doctrines repositories where a *User* and *Blog* object have its own
repositories.

BlueDot uses repositories out of the box. If you have a *configuration.yml* with which you 
constructed BlueDot, that means you are using the *configuration* repository under the hood.
Repository names are derived from the name of the .yml file minus the .yml extension. 

In our *user* and *blog* example, we would have two files:

    user.yml
    
    configuration:
        select:
            get_all_users: 
                sql: SELECT * FROM users;
                
    blog.yml
    
    configuration:
        select:
            get_all_blogs:
                sql: SELECT * FROM blogs;
                
After you create the BlueDot instance, first, tell BlueDot you have a new
repository to be used:

    $blueDot->repository()->putRepository('/path/to/user.yml');
    $blueDot->repository()->putRepository('/path/to/blog.yml');
    
This will create two repositories: *user* and *blog*. To use a repository, use the
*BlueDot::useRepository()* method:

    $blueDot->useRepository('blog');
    
    NOTE: The name of the repository is the name of the file, minus the .yml extension

Now, you can use the queries defined in *blog.yml* configuration. 

Repositories are a good way of organising your queries into logical parts, but they have a
limitation; you can only use one repository at a time. That means, if you are currently using
*blog* repository, you can't execute queries from the *use* repository. You have to
use the *BlueDot::useRepository()* method to switch repositories.

    $blueDot->useRepository('user');
    
    $blueDot->execute('simple.select.get_all_users');
    
    // this one throws an exception since it cannot find the statement
    $blueDot->execute('simple.select.get_all_blogs');
    
    $blueDot->useRepository('blog');
    
    // Now OK
    $blueDot->execute('simple.select.get_all_blogs');
    
Are you going to group your queries into multiple files as repositories or in 
a single file, is up to you.

## 10. Statement builder

Statement builder is a separate tool for executing oneoff sql statement for which
you haven't prepared a configuration or which **BlueDot** cannot execute.

    // when using statement builder, you don't need configuration, only connection
    $blueDot = new BlueDot(null, $connection);
    
    $this->blueDot
        ->createStatementBuilder()
        ->addSql(sprintf('SELECT word_id, translation FROM translations WHERE word_id IN (1, 60, 150, 78, 345)'))
        ->execute()
        ->getResult();

Also, *BlueDot::createStatementBuilder()* receives a *BlueDot\Scenario\Connection*
so you can use it with multiple connection i.e. databases.

Statement builder also supports returning models as a result.

    $this->blueDot
        ->createStatementBuilder()
        ->addSql(sprintf('SELECT word_id, translation FROM translations WHERE word_id IN (1, 60, 150, 78, 345)'))
        ->addModel(Translation::class)
        ->execute()
        ->getResult();
        
If you instantiate **BlueDot** without any parameters (without configuration and connection),
then, you can only use the statement builder if you provide the statement builder with
a connection.

    $this->blueDot
        ->createStatementBuilder($connection)
        ->addSql(sprintf('SELECT word_id, translation FROM translations WHERE word_id IN (1, 60, 150, 78, 345)'))
        ->addModel(Translation::class)
        ->execute()
        ->getResult();
        
## 11. Promise interface and getting the result

#### 11.1 Introduction

Results in BlueDot are accessed trough the *Promise* object that 
*BlueDot::execute()* method returns.

    /** @param BlueDot\Entity\PromiseInterface */
    $promise = $blueDot->execute('simple.select.get_all_users');
    
Promise has these method:

- getArrayResult()
- getEntity(): Entity
- onResultReady(\Closure)

In this chapter, I will try to keep it simple as possible. I will give
an example of what the result might look like and the methods on the
*Entity* object that you can use to get the particular part of the result.

First, lets talk about the *Promise* object.

#### 11.2 PromiseInterface

Lets run a query that will return all users from the database:

    /** BlueDot\Entity\PromiseInterface */
    $promise = $blueDot->execute('simple.select.get_all_users');
    
    
*PromiseInterface::getEntity()* retrieves the *Entity* object that hold the actual
result of the query.

*PromiseInterface::getResultAsArray()* returns the result as an array.

*PromiseInterface::onResultReady()* receives an anonymous function as an argument, that in turn,
receives the *EntityInterface* object as its argument:

    /** BlueDot\Entity\PromiseInterface */
    $promise = $blueDot->execute('simple.select.get_all_users');
    
    $promise->onResultReady(function(EntityInterface $entity) {
        // manipulate the resulting entity here
    });
    
I like anonymous function because the introduce a new scope where you can declare 
variables without fear of them being declared somewhere else. There hasn't been much talk
about scopes in PHP but there should be. For example:

    // lets presume that up to this point you have created
    // a lot of variables here. That would be bad pratice, but follow
    // me here for the sake of the argument.
    
    $someVar = 'string';
    
    $promise->onResultReady(function(EntityInterface $entity) {
        // new scope inside the anonymous function
        
        // $someVar has nothing to do with $someVar declared
        // outside of this function
        $someVar = 'string'
    });
    
    var_dump($someVar); // prints 'string';
    
Now, lets talk about the *EntityInterface* and the *Entity* implementation
of that interface.

#### 11.3 Entity result object

The *BlueDot\Entity\Entity* object is a wrapper around the result that has 
various methods that can help you to get the data from the final result and manipulate
that data with filter methods. Depending on the query, some information may be available or
not. For example, if you execute an select query, you will have a data property that will
hold the fetched data but you will also have a number of rows as *row_num*. *data* property
will not be available in *delete*, *update* or *insert* queries. 

On another hand, in an insert query, you will get a *last_insert_id* and *row_count*, but also
the *inserted_ids* field with all the inserted ids if the query was executed multiple times with 
multiple parameters.

Also, scenario statements will return the same data but under the key that is the name of the scenario.
It will also not be a *Entity* object but a *BlueDot\Entity\EntityCollection* object that will hold all
the entities associated with each scenario executed.

##### Simple statements

Given this result:

    [
        0 => ['name' => 'Billie', 'last_name' => 'Holliday'],
        1 => ['name' => 'Dolly', 'last_name' => 'Parton'],
        2 => ['name' => 'Katie', 'last_name' => 'Melua']
    ]
    
The resulting *Entity* object would hold this data:

    [
        'rows_num' => 3
        'type' => 'simple',
        'data' => // the data from the upper example
    ]
    
You can get the *row_count* with the method *Entity::getRowCount()*.

You can get the type of the statement with the method *Entity::getType()*.

But there is no method to get the data from the *Entity* object. That is because *insert*,
*update* and *delete* statements don't have any data to get.

In order to get the data, you will have to call the method *Entity::toArray()* and get 
the data on the *data* index.

    $blueDot
        ->execute('simple.select.get_all_users')
        ->onResultReady(function(EntityInterface $entity) {
            $data = $entity->toArray()['data'];
        });
        
    // OR
    
    $data = $promise->getEntity()->toArray()['data'];
    
#### Scenario statements

Scenario statements are the same as simple statement but only to relation
to some particular scenario statement.

For example:

    $promise = $blueDot->execute('scenario.my_scenario');
    
    /** BlueDot\Entity\EntityCollection */
    $entityCollection = $promise->getEntity();
    
    $scenarioEntity = $entityCollection->getEntity('scenario_name');
    
Everything else is the same as in simple statements.

#### Service statements

Service statements are used for executing multiple scenario, simple statement or multiple
service statements in one logical place. Because of that, anything you return from the
*run()* method, will be in the *data* property of the *Entity* object and in that regard,
has no difference between the scenario result or simple statement result.

## 12. Filters

Filters are a way of filtering out the result after a certain statement was 
executed. You can use it to find a certain entry in a list of entries, extract a single
column out of a list of entries or link columns in a one-to-many relationship.

It is important to note that filters do **not** work with object but only with
plain arrays.

Filters can be found on the result of the **Entity** object

#### 12.1 A list of possibilities

**Very important**

Every *Entity* object is immutable. That means that after you apply a filter on
a result, you will get a new *Entity* object back which preserves the original result.

**Entity::findBy($columns:string, $value:any): array**

Given a *$column* name and a value of the column, returns an array of found values.
     
For example:
     
If the result of a query is:

          [
              0 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
              1 => ['name' => 'Katie', 'last_name' => 'Melua' ... other fields ]
              2 => ['name' => 'Billie', 'last_name' => 'Holiday' ... other fields ]
              3 => ['name' => 'Dolly', 'last_name' => 'Parton' ... other fields ]
              4 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
          ]

if you call $entity->findBy('name', 'Natalia'), this method will return a list
of all results that have 'Natalia' in the name column. It is important to say that 
this method will return an *array* of numerically indexed entries even if there is only
one entry.
 
In this example, it will return 2 entries.

**Entity::find($column:string, $value:any): array**

This method returns a single entry only if a single entry actually exists in the array
of results. If we have a result like this one...

      
     [
         0 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
         1 => ['name' => 'Katie', 'last_name' => 'Melua' ... other fields ]
         2 => ['name' => 'Billie', 'last_name' => 'Holiday' ... other fields ]
         3 => ['name' => 'Dolly', 'last_name' => 'Parton' ... other fields ]
         4 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
     ]

*$entity->find('name', 'Natalia')* will throw an exception because there are 2 rows that have
the string 'Natalia' as the value of the name column. Wrap this method into a try/catch
clause to avoid the exception.

**Entity::extractColumn($column:string): array**

Entity::extractColumn() returns all entries of one column.

     [
         0 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
         1 => ['name' => 'Katie', 'last_name' => 'Melua' ... other fields ]
         2 => ['name' => 'Billie', 'last_name' => 'Holiday' ... other fields ]
         3 => ['name' => 'Dolly', 'last_name' => 'Parton' ... other fields ]
         4 => ['name' => 'Natalia', 'last_name' => 'Natalie' ... other fields ]
     ]

$entity->extractColumn('name') will return a list of all name properties like this...


     [
         'name' => ['Natalia', 'Katie', 'Billie', 'Dolly', 'Natalia']
     ]
     
**Entity::normalizeJoinedResult($grouping:array, $columns:array): array**
     
This is a special method that deals only with relationships between MySQL tables.
If you have a one-to-many relationship, use this method to group the fields in a natural 
way.
 
For example, if we have a table *words* and a table *translations* and *translations* table
has a many-to-one relationship to *words*, execution a join sql query will result in the number of rows
that *translations* table has even if *words* has only one row for a search word.
      
     SELECT w.id, w.name, t.translation FROM words AS w INNER JOIN translations AS t ON w.id = t.word_id AND w.id = 1;
    
If *translations* has 10 rows for a single word, this query would return 10 rows with identical information
from the *words* table rows and different translations. This is not what we want.

To group this result, use 
     
     $entity->normalizeJoinedResult([
         'linking_column' => 'id',
         'columns' => ['translations']
     ]);

*linking_column* tells us the relationship between the rows. It the above example, *id* would be identical for
all rows since it is the *id* of the *words* table. *columns* are the columns you would like to group. The result is
 
     [
         'id' => 1,
         'name' => 'word_name',
         'translations' => [
             'translation1',
             'translation2',
             ... other translations
         ]
     ]

You can choose as much columns as you like.

#### Chaining filters

Filters can be chained to get to a wanted result. If we wanted to extract
all the translations from the *Entity::normalizeJoinedResult()* example into a single
associative array, we could use

    $entity
        ->normalizeJoinedResult([
            'linking_column' => 'id',
            'columns' => ['translations'],
        ])
        ->extractColumn('translations')
        ->normalizeIfOneExists()
        
The result of these methods would be a single array with all the found translations.

You can also use this example with this SQL query

    SELECT w.id, w.name, t.translation FROM words AS w INNER JOIN translations AS t ON w.id = t.word_id;
    
This is the same example that we used with *Entity::normalizeJoinedResult()* but we dropped
the *AND id = 1* which gives us all the translations of a single word. In the new example, we want
the translations of all the words that have a translation. If, for example, we have 3 words
with 10 translations for each, the above query would return 30 rows, one row for each translation.

So, first we are going to normalize the result to group all the translations for a particular word into
a single *translations* field. After that, we find the the word with *id = 2* and normalize the result
to return a single string key based array.

    $entity
        ->normalizeJoinedResult([
            'linking_column' => 'id',
            'columns' => ['translations'],
        ])
        ->find('id', 2);
        ->normalizeIfOneExists()
        
#### 12.2 Using configuration filters

You can use all the filters in configuration also, but there is one downside.
If you read the entire chapter on filters, you know that using filters in code with the
*Entity* object preserves the original *Entity* object. Every filter method returns a new 
object which makes the *Entity* object immutable.

That is not the case with configuration entities. If you choose to apply a filter (or chain filters) in
configuration, the original result is lost.

You can use filters in configuration like this:

    normalize_joined_result_find_all_users:
        sql: SELECT * FROM users
        filter:
            by_column: 'id'
            
*by_column* is an alias for *Entity::extractColumn()* when used in code. This will have
the same effect as calling *Entity::extractColumn()*.

You can also chain the filters in configuration:

    normalize_joined_result_find_all_users:
        sql: select.find_all_users
        filter:
            normalize_joined_result:
                linking_column: 'id'
                columns: ['username']
            find: [id, 7]
            normalize_if_one_exists: true
            
*Please note that normalize_if_one_exists has to receive the boolean true or false*

When used in configuration, all filters are grouped and sent to an observer pattern that does
not care about the original result, only the result it gets from the next executing filter.

The choice of using filters in code or in the configuration is up to you. If you do not need to preserve
the original result, use configuration filters. If you need the original result but also the results
of filters, use the filters in code as method of the *Entity* object. The choice is yours.
     
## 13. Imports

Imports are a way of centralizing all your sql queries into one .yml file. Path to that file is injected
via *sql_import* configuration option.

    sql_import: sqls.yml
    
That file must be a relative path relative to given configuration value. The file should look like this...

    your_unique_name: "some sql"

    some_unique_namespace:
        another_unique_namespace:
            yet_another_unique_namespace:
                sql_1: "some_sql"
                sql_2: "some_sql"
                
You specify the import with its name separated with a dot under the *sql* config option. To return to the previous
example

    sql_import: relative_path_config.yml

    scenario:
        atomic: true
        return_data: ['select_user.name', 'select_user.lastname', 'select_user_prefs.purchase_history']
        select_user_data:
            statements:
                select_user:
                    sql: "SELECT id, name, lastname, username FROM user WHERE user_id = :id"
                    parameters: [id]
                select_user_pref:
                    sql: "SELECT * FROM user_preferences WHERE id = :id"
                    use:
                        statement_name:
                        values: {select_user.id: id}
                        
sql queries could be represented like this...

    my_scenarious:
        user_queries:
            select_user: "SELECT id, name, lastname, username FROM user WHERE user_id = :id"
            select_user_prefs: "SELECT * FROM user_preferences WHERE id = :id"
            
In configuration, this import would look like this...

    sql_import: relative_path_config.yml

    scenario:
        atomic: true
        return_data: ['select_user.name', 'select_user.lastname', 'select_user_prefs.purchase_history']
        select_user_data:
            statements:
                select_user:
                    sql: my_scenarious.user_queries.select_user
                    parameters: [id]
                select_user_pref:
                    sql: my_scenarious.user_queries.select_user_prefs
                    use:
                        statement_name:
                        values: {select_user.id: id}
                
## 14. Conclusion

Although **BlueDot** makes executing sql queries easy, it is not here to replace Doctrine or similar tools.
I recommend using **BlueDot** when you have to make complex sql queries when using a DBAL would be an overhead.
**BlueDot** can be used to create complete applications but not every application should be using **BlueDot**
exclusively. For example, if you have an application that has a lot of forms that need to be inserted and updated,
**BlueDot** is probably not for you. But, if you have a complex search feature that selects a lot of data
from many tables, use it. 

You can also use **BlueDot** together with Doctrine or similar tools. Doctrine creates its own PDO connection.
If you leave out *connection* configuration values, you can init **BlueDot** with doctrines connection and use 
it together with **BlueDot**. 

    use BlueDot\Kernel\Connection\ConnectionFactory;
    
    $doctrinePdoConnection = // get the PDO object from doctrine here
    
    $connection = ConnectionFactory::create([]);
    $connection->setPDO($doctrinePdoConnection);
    
    $blueDot->setConnection($connection);
    
Now, **BlueDot** and Doctrine are both using the same connection. If BlueDot used
a previous PDO object from somewhere else, that connection is after you call
*BlueDot::setConnection()*

## 15. Setting up tests

To setup tests, create a database called *blue_dot* with the username *root* and
password *root*. Locally, I use simple passwords. If you have something else
setted up, go into the *tests/config/result* directory and find these files:

*prepared_execution_test.yml*
*simple_statement_test.yml*
*scenario_statement_test.yml*

and change the connection settings in those files under the *configuration > connection* node
and you are all set to run tests locally.




                    
                   
    


    

        









 
 














