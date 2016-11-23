##BlueDot##
*Pure sql database abstraction layer*

##Content##

1. Introduction
2. Installation
3. The basics
    * Initial configuration
    * Database connection
    * Simple sql statements
4. Simple statements
    * Parameters explained
    * Multi inserts
5. Scenario statements
    * How it works
    * 'use' configuration feature
    * 'foreign_key' configuration feature
    * Parameters explained
    * Returning results
6. Callable statement
7. Configuration reference

###1. Introduction###

**BlueDot** is a database abstraction layer that works with pure sql but returns domain objects that you can work with. It's configuration based and requires minimal work and setup to start working with it. The reason I created this tool is simple free time. Hope someone will find it useful.

###2. Installation###

**BlueDot** requires PHP 7.0 or higher

Install it with [composer](https://getcomposer.org/)

    composer require mario-legenda/blue-dot
    
###3. The basics###

3.1 Initial configuration

**BlueDot** is fully configurable trough .yml configuration file that you specify in the ```BlueDot``` constructor. 

    use BlueDot\BlueDot;
    
    $blueDot = new BlueDot('/path/to/config/file/configuration.yml');
    










