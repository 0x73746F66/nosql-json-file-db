noSQLDB
=======

PHP noSQL JSON DB API

# Usage

## Instantiate

Create an instance and set the path to the database, defaults to this directory.

`$db = new db( 'db' );`

## Prepare

Prepare a new user record.

```
$newuser['username'] = 'chrisdlangton';

$newuser['email'] = 'chris@codewiz.biz';

$newuser['active'] = true;
```

OR

```
$newuser = array(
    'username' => 'chrisdlangton',
    'email' => 'chris@codewiz.biz',
    'active' => true
);
```

## Insert

Insert a new user (will also create the 'table').

```
$db->insert( 'users' , $newuser );
```

## Select

Query all records in 'table'.

```
$db->select( 'users' )
    ->get();
```

Query all records in 'table' that have a 'column' matching x but the value of column x may be anything as long as column x exists.

```
$db->select( 'users' , 'email' )
    ->get();
```

Query all records in 'table' that have a 'column' with values matching an array of columns and values.

```
$db->select( 'users', array( 'username'=>'chrisdlangton' , 'active'=>true , 'email'=>'chris@codewiz.biz' ) )
    ->get();
```

Select by primary key id.

```
$db->select( 'users' )
    ->id( 5 );
```

Select all distict values in a table by column name.

```
$db->select( 'users', 'email' )
    ->distinct();
```

## Update

Prepare an update, use get() to view the results of the select.

```
$db->select( 'users' , 'email' )
    ->update( array( 'username'=>'testing' ) )
    ->get();
```

Prepare an update, use get( 'update' ) to view the results of the update before committing.

```
$db->select( 'users' , 'email' )
    ->update( array( 'username'=>'testing' ) )
    ->get( 'update' );
```

Prepare an update, use commit() to commit the changes to the db.

```
$db->select( 'users' , 'email' )
    ->update( array( 'username'=>'testing' ) )
    ->commit();
```

OR

```
$db->select( 'users' , 'email' )
    ->update( array( 'username'=>'testing' ) )
    ->commit();
```

Prepare an update with multiple changes, use commit() to commit the changes to the db.

```
$db->select( 'users' , 'email' )
    ->update( array( 'username'=>'testing','active'=>false ) )
    ->commit();
```

OR

```
$db->select( 'users' , 'email' )
    ->update( array( 'username'=>'testing','active'=>false ) )
    ->commit();
```

## Delete

Prepare a delete, use get() to view the original table data.

```
$db->delete( 'users' , array( 'username'=>'testing' ) )
    ->get();
```

Prepare a delete, use get( 'delete' ) to view the new table data before commiting.

```
$db->delete( 'users' , array( 'username'=>'testing' ) )
    ->get( 'delete' );
```

Prepare a delete, use commit() to commit the changes to the db.

```
$db->delete( 'users' , array( 'username'=>'testing' ) )
    ->commit();
```

OR

```
$db->delete( 'users' , array( 'username'=>'testing' ) )
    ->commit();
```

Prepare a delete with multiple changes, use commit() to commit the changes to the db.

```
$db->delete( 'users' , array( 'active'=>true , 'username'=>'chrisdlangton' ) )
    ->commit();
```

OR

```
$db->delete( 'users' , array( 'active'=>true , 'username'=>'chrisdlangton' ) )
    ->commit();
```

## Order By

### ASC

```
$db->select( 'users' )
    ->order( 'username' );
```

### DESC

```
$db->select( 'users' )
    ->order( 'username', 'true' );
```

## Reset

```
$db->reset('users);
```
