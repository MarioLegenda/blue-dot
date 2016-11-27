###BlueDot###
*Pure sql database abstraction layer*

###Content###

1. Introduction
2. Installation
3. The basics
    * Initial configuration
    * Database connection
    * Simple sql statements
4. Simple statements
    * Parameters explained
    * Atomic inserts, updates and deletes
5. Scenario statements
    * How it works
    * 'use' configuration feature
    * 'foreign_key' configuration feature
    * Parameters explained
    * Returning results
6. Callable statement
7. Results
8. Configuration reference

###1. Introduction###

**BlueDot** is a database abstraction layer that works with pure sql but returns domain objects that you can work with. It's configuration based and requires minimal work and setup to start working with it. The reason I created this tool is simple free time. Hope someone will find it useful.

###2. Installation###

**BlueDot** requires PHP 7.0 or higher

Install it with [composer](https://getcomposer.org/)

    composer require mario-legenda/blue-dot
    
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
            
And you are all set to make your first query to the database. The tests in this repository use the ```world``` database from mysql.

**3.3 Simple sql statements**

First, let's create a ```simple``` sql query in the configuration and run with **BlueDot** (```simple``` statements will be explained in detail in later chapters)

    configuration:
        connection:
            host: localhost
            database_name: world
            user: root
            password: root
            
        simple:
            select:
                get_all_cities:
                    sql: "SELECT * FROM city"
                
In your code, instantiate **BlueDot** and run the ```BlueDot::execute()``` method with notation ```simple.select.get_all_cities```

    $resultEntity = $blueDot->execute('simple.select.get_all_cities')->getResult();
    
```$resultEntity``` will be and ```EntityCollection``` object that will contain all cities (more about working with ```Entity``` later)
And that is it. Configure and execute.

###4. Simple statements###

Simple statements are what the name describes. Simple. They execute only one sql statement that you specify. It the example from
the chapter above, it executed ```get_all_cities``` and returned the result.

There are four types of simple statements. ```select```, ```insert```, ```update``` and ```delete```. If there is a ```simple```
configuration value present, there has to be at least one of the above configuration values present. The name of the statement can
be anything you like. In the above example, it is ```get_all_cities```

**4.1 Parameters explained**

If your sql statement has parameters, you configure them and pass them as the second parameter to ```BlueDot::execute()``` method.

    configuration:
        connection:
            host: localhost
            database_name: world
            user: root
            password: root
            
        simple:
            select:
                get_city_by_name:
                    sql: "SELECT * FROM city WHERE name = :name"
                    parameters: [name]
                    
And in your code

    $blueDot->execute('simple.select.get_city_by_name', array(
        'name' => 'Split'
    ));
                  
The parameters all have to have the same name. That means, if you specified ```:name``` in the sql query, then that string value
has to be in the ```parameters``` configuration entry and also as an entry in the second argument of ```BlueDot::execute()```
method.

Same goes for insert, update, and delete statements. 

**4.2 Atomic inserts, updates and deletes**

If you wish to have multiple atomic insert, update and delete statements, you provide the values as the second argument of
```BlueDot::execute()```

For example

    simple:
        insert:
            insert_user:
                sql: "INSERT INTO user (name, lastname) VALUES (:name, :lastname)"
                parameters: [name, lastname]
                
If you call this statement like this

    $blueDot->execute('simple.insert.insert_user', array(
        'name' => array(
            'Zoey',
            'Brittany',
            'Michelle',
        ),
        'lastname' => array(
            'Deschanel',
            'Murphy',
            'Gomez',
        )
    ));
    
it will execute ```insert_user``` three times, one for each name and lastname value. The number of values have to be the same. That is,
if you provide three values for ```name```, you have to provide three values for ```lastname```, even if they are null. Same applies
for update and delete statements.

###5. Scenario statements###

**5.1 How it works**

Scenario statements are statements that are meant to be executed in bulk, together as one unit of work. 

Let's image a webshop that sells clothes. It's frontend has a search feature that the user can search various clothes with it.
We could have a table ```category``` and ```product``` with which we use an ```inner join``` to collect product data.
But, after the product has been fetched from the database, we would like to save some preferences data to our 
```user_preferences``` table. So, we need to make one query to find the product that best matches the search input, then, 
make an inner join with our ```category``` table, select the logged in user and save the data to our ```user_preferences```
table. So let's do all that with a scenario...


    configuration:
        connection:
            host: localhost
            database_name: search_application
            user: root
            password: root
            
        scenario:
            search:
                atomic: true
                return_entity: []
                statements:
                    find_product:
                        sql_type: select
                        sql: "SELECT name, description, price, category_id FROM product WHERE name LIKE :name"
                    find_by_category:
                        sql_type: select
                        sql: "SELECT p.name, p.description, p.price, c.name FROM product AS p INNER JOIN category AS c ON p.category_id = category_id"
                        use: 
                            statement_name: find_product
                            values: [find_product: category_id]
                    # this is the currently logged in user
                    find_user:
                        sql_type: select
                        sql: "SELECT * FROM user WHERE id = :id"
                    create_preference:
                        sql_type: insert
                        sql: "INSERT INTO user_preferences (user_id, category_id, product_id) VALUES (:user_id, :category_id, :product_id)
                        use:
                            















