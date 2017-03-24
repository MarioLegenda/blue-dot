## BlueDot
*Pure sql database abstraction layer*

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
7. Callable statements
8. Statement builder
9. Promise interface
    * Simple statement promise
    * Scenario statement promise
    * Callable promise
10. Imports
11. BlueDot internals explained
12. Conclusion
13. Configuration reference

## 1. Introduction

**BlueDot** is a database abstraction layer that works with pure sql but returns 
domain objects that you can work with. It's configuration based and 
requires minimal work and setup to start working with it. The reason I 
created this tool is simple free time. Hope someone will find it useful.

This documentation is written in a way in which you will first learn how
to execute sql queries but getting the result and manipulating it is
covered in *Chapter 9: Promise interface*

## 2. Installation

**BlueDot** requires PHP 7.0 or higher

Install it with [composer](https://getcomposer.org/)

    composer require mario-legenda/blue-dot 1.0.0
    
## 3. The basics

#### 3.1 Initial configuration

**BlueDot** is fully configurable trough .yml configuration file that you specify in the ```BlueDot``` constructor. 

    use BlueDot\BlueDot;
    
    $blueDot = new BlueDot('/path/to/config/file/configuration.yml');
    
You can also instantiate via singleton

    use BlueDot/BlueDot
    
    $blueDot = BlueDot::instance('/path/to/config/file/configuration.yml');
    
#### 3.2. Database connection

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
- callable

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
A promise can be a **success** or a **failure**. If the query returned an empty
result, the statement is a failure. If it returned some results, then it is a success.

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
        'id': 6,
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
    
**BlueDot** concludes from configuration that you want a language parameter to 
be bound to the statement sql query. It then concludes that you supplied an object
as a parameter and looks for a *Language::getLanguage()* method on that object.
If it finds one, it binds the value returned from that method to the *language*
parameter of the sql query.

It is important to say that there has to be a *get* method on the model for the parameter(s)
that you want to bind. For example, if you also need to bind a *name* parameter,
there has to be a *Language::getName()* parameter on the *Language* model.

Model binding is a two-way process and it can be used to fetch models from the database.
For example, to expand on our *users* example, you could have a *User* with fields *id*, *name*, *username* and *password*.
You would like to pass the user object as a parameter but also return a populated *User*
model from the database.
 
    simple:
        select:
            find_users:
                sql: "SELECT * FROM users"
                model:
                    object: App\Model\User
                    
    $blueDot->execute('simple.select.find_user');
    
**BlueDot** will return an array of *User* objects populated with the value for
*id*, *name*, *username* and *password*.

You can combine these to approaches to find a specific user...

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
*get* and *set* method for that column. For example, if a table contains a column date_created
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
                        statement_name: create_word
                        bind_to: word_id
                    
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

This is a simple example, but it could be tedious work if multiple insert statements are 
necessary. With scenarios, this is a trivial task. 

First, *create_word* statement is executed and *last_insert_id* is saved internally. Then, 
execution goes to execute *create_translations* statement. **BlueDot** sees that the statement
has a *foreign_key* option. The option consists of a statement name and the name of the parameter
to bind *last_insert_id* to. In the above example, that statement is *create_word* and the parameter
is *word_id*. If *foreign_key* statement is not executed, **BlueDot** executes it. If it is, it 
proceedes to execute the current statement. In execution, **BlueDot** binds the parameter *word_id*
of statement *create_translations* to *last_insert_id* of *create_word* statement and executes.

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

*foreign_key* option works even if you execute multiple insert statements. Consider the following example

    $blueDot->execute('scenario.create_word', array(
        'create_word' => array(
            'word' => array('word 1', 'word 2'),
        ),
        'create_translations' => array(
            'translations' => array('translation 1', 'translation 2', 'translation 3'),
        )
    ));
    
Here, *create_word* will be executed two times but only the last *last_insert_id* will be used
in *create_translations* statement. *Don't forget that* because you could get some unexpected
result that you may not want.

#### 6.5 'if_exists' and 'if_not_exists' configuration option

You have already seen examples of *if_exists* and *if_not_exists* options. These options
tell **BlueDot** to execute or not to execute a statement if some other statement exists.

Both options can be used with any sql query but there is a catch. In Mysql, if an update does
not change any information, **BlueDot** cannot know if a statement is executed or not.
Internally, for *insert*, *update* or *delete* sql queries, it call the *PDOStatement::rowCount()* method to see how many rows have been
affected. If no rows have been affected, that kind of statement will be considered a nonexistent.
Don't forget that when you use those sql queries with these options.

## 7. Callable statements

A callable statement is an object that extends *BlueDot\Common\AbstractCallable*.
In that object, you will have **BlueDot** object and parameters array that you could
use as a dependency injection container. It is best to see it in an example.

    callable:
        my_callable:
            type: object
            name: Some\Namespace\Callable
        
    class MyCallable extends BlueDot\Common\AbstractCallable 
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

Callable has a *run()* method that **BlueDot** executes. Poupuose of callable
is to group many scenarios or simple statements together. The return value of a callable
is anything that *run()* method returns but encapsulated in a Promise. More on promises
later.

## 8. Statement builder

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
        
## 9. Promise interface

#### 9.1 Simple statement promise

So far, you have only seen how to execute sql queries and statements. In this chapter,
you will learn how to use the Promise interface and manipulate results.

Promise in **BlueDot** work similary as promises in javascript. When a statement is executed,
it produces a certain result. *insert* statement produces a *last_insert_id* and the number of
rows affected by the query. *update*, *delete*, *alter* etc. produce only the number of
affected rows. *select* statements return a result or an empty array if no result was found.
Based on those data, a promise could be a success or a failure.

Consider this example...

    $promise = $blueDot->execute('simple.select.get_all_users');
    
The return value of *BlueDot::execute()* is a *BlueDot\Entity\Promise* object that implement
*BlueDot\Entity\PromiseInterface*. That interface has *success* and *failure* methods that
accept an anonymous function that is to be executed if a statement was a success or a failure.

    $promise = $blueDot->execute('simple.select.get_all_users');
    
    $promise
        ->success(function(PromiseInterface $promise) {
            // this function will execute if the statement retured some results
        })
        ->failure(function(PromiseInterface $promise) {
            // this function will execute if the statement did not produce any result
        });
        
Let's presume that *simple.select.get_all_users* returned all the users of your application.
You would access the result of that statement in the *success()* anonymous method.

    $blueDot->execute('simple.select.get_all_users')
        ->success(function(PromiseInterface $promise) {
            $users = $promise->getResult();
            
            foreach ($users as $user) {
                echo $user['name']
            }
        });
        
*PromiseInterface::getResult()* method returns a *BlueDot\Entity\Entity* object that acts like
an array so you can access it like an array. It also has various helper methods for filtering results.

You don't have to use *Promises* to access the result of a statement.

    $users = $blueDot->execute('simple.select.get_all_users')->getResult();

    foreach ($users as $user) {
        echo $user->get('name');
    }
    
Remember, *PromiseInterface::getResult()* returns an Entity object. That object has a Entity::get() 
method with which you can access result by column name. You can also access it as a plain array.

The *Entity* object has a couple of helper methods to filter the results. If there are multiple
results returned from your statement, you can use *Entity::findBy()* method to filter the result.

    $user = $blueDot
                ->execute('simple.select.get_all_users')
                ->getResult()
                ->findBy(array(
                    'id' => 6,
                ));
                
    echo $user[0]['name'];
        
*$user* variable will contain an Entity object of a user with id 6. When using *Entity::findBy()*,
a new *Entity* object is returned that has a zero indexed array internally. Because of that, this
won't work...

    echo $user->get('name');
    
    // but this will work
    
    echo $user[0]['name'];
    
Don't forget that when using *Entity::findBy()*.

If there is only one user returned from the result, you can use *Entity::normalizeIfOneExists()* 
method to return an associative array with the user.
  
    $user = $blueDot
                ->execute('simple.select.get_all_users')
                ->getResult()
                ->findBy(array(
                    'id' => 6,
                ))
                ->normalizeIfOneExists();
                
    echo $user['name'];
    
There is also an *Entity::find()* method that is used to find only a single result based on its
column name and column value.

    $user = $blueDot
                ->execute('simple.select.get_all_users')
                ->getResult()
                ->find('id', 6);
                
    echo $user['name'];
    
If the *Entity::find()* method finds more that one result, it will throw an exception.

Then, there is the *Entity::extract()* method. This method will extract a single columns
results if multiple results are returned.

    $users = $blueDot
                 ->execute('simple.select.get_all_users')
                 ->getResult()
                 ->extract('id');
                 
    // $users now contains an array of ids indexed by an 'id' key
    
    $ids = $users['id'];
    
    foreach ($ids as $id) {
        echo $id;
    }
    
There is also a special method for working with *one-to-many* relations. In the example
with words and translations, there is only one word with many translations. If you used an
*INNER JOIN* to fetch results in one row, you would get many rows with only unique *translation*
row.

For example...

    SELECT w.id, w.word, t.translation FROM words AS w INNER JOIN translations AS t ON t.word_id = w.id WHERE w.id = 6
    
If there are 5 translations of a word, this query would return an array with 5 members. That is not
the desired format. Desired format would be to return an associative array with a *translation*
key under which all the translations would be. For this, you can use *Entity::arrangeMultiples()*.

    $blueDot->execute('simple.select.select_translations', array(
                  'id' => 6,
              )
              ->success(function(PromiseInterface $promise) {
                  $arrangedResult = $promise->getResult()->arrangeMultiples(array('translations'));
              });

This method would arrange the array so that *translation* would be a key under which all the translations
are as an array of values. If there are multiple columns to arrange, you can add another member to the first
argument of *Entity::arrangeMultiples()*. This method also has more arguments to filter the result.

If some column values don't have to be in the result array, you can add a second argument which is a anonymous
function that has to return *true* or *false*. If true, evaluated row would be in the result array. If not,
it would be skipped. This function accepts currently iterated row as an argument.

    $promise
        ->getResult()
        ->arrangeMultiples(array('translation'), function($row) {
            // add a translation of the currenlty iterated row only if 'name' column is not empty
            return !empty($row['name']);
        });
        
There is also a special feature to promises. If you noticed, the *PromiseInterface::getResult()* method
returns the result of the executed statement. This method holds a special feature when used in 
anonymous promise function. 

    $result = $promise
        ->success(function(PromiseInterface $promise) {
            $normalizedResult = $promise->getResult()->normalizeIfOneExists();
            
            return $normalizedResult;
        })
        ->failure(function() {
            return 'statement failed';
        })
        ->getResult();
        
If you remember from the above examples, *Entity::getResult()* would return a result of the executed
statement. But if you would, for any reason, want to return some other result, any return data
that you return from *success* or *failure* promises would be the actual result. In the above example,
on success, *Entity::getResult()* would return the return value of *success* callback. On failure, it would
return a string 'statement failed'. If you which to access the original *Entity* returned from the 
executing statement, use *Entity::getOriginalEntity()*.

    // if success, $result contains the returned value of *success* callback

    $result = $promise
        ->success(function(PromiseInterface $promise) {
            $normalizedResult = $promise->getResult()->normalizeIfOneExists();
            
            return $normalizedResult;
        })
        ->failure(function() {
            return 'statement failed';
        })
        ->getResult();
            
    // $originalResult contains the originaly returned Entity 
    $originalResult = $promise->getOriginalEntity();
    
So far, you have only seen fetching results from *select* statements. *insert*, *delete* and
other statements behave in a similar way. Take a look at an example...

    // there are two fields for insert statements
    $blueDot->execute('simple.insert.create_user')
        ->success(function(PromiseInterface $promise) {            
            echo $promise->getResult()->get('last_insert_id');
            echo $promise->getResult()->get('row_count');
        })
        
    // but there is only one field for update, delete, modify or alter
    $blueDot->execute('simple.delete.delete_user')
        ->success(function(PromiseInterface $promise) {
            echo $promise->getResult()->get('row_count');
        });
    
*IMPORTANT*

If a *delete*, *update* or some other sql query does not modify any rows (update doesn't update, delete
does not delete any row), above statement would be a failure. Don't forget that.

For convenience, there are also *PromiseInterface::isSuccess()* and *PromiseInterface::isFailure()*
methods to check a statements promise.

    $promise = $blueDot->execute('simple.select.get_all_users');
    
    if ($promise->isSuccess()) {
       // success code goes here
    } else if ($promise->isFailure()) {
       // failure code goes here
    }
    
#### 9.2 Scenario statement promise

Scenario statement promises work in a similar way as simple statement but with one difference.
Scenario statement consist of one or more individual statements. Those statements can be any of
*select*, *insert* or *update* statements. By default, scenario statements return information about
every executed statement.

*insert* statements return *last_insert_id* and *row_count*. *row_count* holds the number or rows 
inserted. *update* and *delete* only return *row_count*. Also, every result of *select* statements
would be returned.

**IMPORTANT**

*update* **and** *delete* **statements that do not change data on the database are regarded as a 
failure and do not return any data**

There is a special configuration value *return_data* for scenario statements.

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
                    
    $blueDot->execute('scenario.select_user_data', array(
        'select_user' => array(
            'id' => 6,
        ),
    ))
    ->success(function(PromiseInterface $promise) {
        $result = $promise->getResult();
    });
    
For this example, we have two tables, *users* and *user_preferences* but we don't need all the
data from both tables but only specific data. For that reason, we use *return_data* configuration value.
This configuration value will select only the columns from any select statement in this scenario that
you specify. You can put as many column values as you wish in *return_data*.

You can also use an alias in *return_data*.

    return_data: ['select_user.name AS user_name', 'select_user.lastname AS user_lastname']
    
**BlueDot** would then return the *name* column from *users* table as *user_name* and other columns as
their aliases also. 

**IMPORTANT**

**If you specify** *return_data* **option, only that data will be returned.**




                    
                   
    


    

        









 
 














