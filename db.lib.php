<?php
class db
{
	private static $path;
	private $table;
	private $data = array();
	public $result = false;
	private $updated = array();
	private $deleted = array();

	public function __construct( $path = null )
	{
        !empty( $path ) && is_string( $path ) ? $this->path = (string)$path : $this->path = '.' ;        
        return $this;
    }
    public function select( $table , $where = null )
    {
        $this->table = $table;
        $this->data = array();
        $this->result = false;
        $key = false;
        if ( is_readable( $this->path . "/" . $this->table . ".json" ) ) 
        {
            $table_arr = json_decode( file_get_contents( $this->path . "/" . $this->table . ".json" ), true ) ;
            if ( empty( $where ) ) {
                $this->data = $table_arr ;
                $this->result = true;
            } elseif ( is_string( $where ) ) {
                $this->key = $where ;
                foreach( $table_arr as $row ) 
                {
                    if ( array_key_exists( $this->key , $row ) ) {
                        $this->data[] = $row ;
                        $this->result = true ;
                    }
                }
            } elseif ( is_array( $where ) ) {
                $keys = array_keys( $where );
                
                foreach( $table_arr as $row ) 
                {
                    if ( array_key_exists( $keys[0] , $row ) ) {
                        $matches_arr[] = $row ;
                    }
                }
                for( $i = 0; $i < count( $keys ); ++$i ) 
                {
                    foreach($matches_arr as $match)
                    {
                        if ( array_key_exists( $keys[$i], $match ) && $match[$keys[$i]] == $where[$keys[$i]] ) {
                            $results[] = $match;
                        }
                    }
                }
                if ( count( $results ) > 0 ) {
                    $this->data = $results;
                    $this->result = true;
                }
            }
        }
        return $this;
    }
    public function id( $pk )
    {
		//after a select full table, search for a record with pk matching $pk to return
		foreach( $this->data as $row )
			if ( array_key_exists( 'pk' , $row ) && $row['pk'] === $pk ) 
				return $row ;
		return false ;
    }
    public function get( $method = 'select' )
    {
        switch ($method)
        {
            case "select":
            return $this->result ? $this->data : $this->result ;
            break;
            case "update":
            return $this->updated ;
            break;
            case "delete":
            return $this->deleted ;
            break;
        }
    }
    public function insert( $table , $record )
    {
        if ( is_readable( $this->path . "/" . $table . ".json" ) ) 
        {
            $data = json_decode( file_get_contents( $this->path . "/" . $table . ".json" ), true ) ;
			if ( array_key_exists( 'pk' , $record ) ) {
				foreach( $data as $row )
				array_key_exists( 'pk' , $row ) && $record['pk'] != $row['pk'] ? true : $return = false ;
				if ( $return === false ) return false ;
			} else {
				$this->key = 'pk' ;
				$this->data = $data ;
				$max = $this->distinct() ;
				$record['pk'] = max( $max ) + 1 ;
				unset( $this->key ) ;
				$this->data = array() ;
			}
        } else {
			$data = array();
			if ( !array_key_exists( 'pk' , $record ) ) {
				$record['pk'] = 1;
			}
		}
        $data[] = $record;
        return file_put_contents( $this->path . "/" . $table . ".json", json_encode( $data ) ) ? true : false ;
    }
    private function replace( &$value , $key )
    {
        if( $key == $this->find )
            $value = $this->replace;
    }
    public function update( $where )
    {
        $this->updated = $this->data;
        if ( is_array( $where ) )
        foreach ( $where as $key => $value )
        {
            $this->find = $key;
            $this->replace = $value;
            array_walk_recursive( $this->updated , array( $this , 'replace' ) ) ;
        }
        return $this ;
    }
    public function rollback()
    {
        unset( $this->table );
        $this->data = array();
        $this->result = false;
        $this->updated = array();
        $this->deleted = array();
    }
    public function commit()
    {
        if ( is_readable( $this->path . "/" . $this->table . ".json" ) ) 
        {
            if ( !empty( $this->updated ) && is_array( $this->updated ) && count( $this->updated ) > 0 ) {
                $table_arr = json_decode( file_get_contents( $this->path . "/" . $this->table . ".json" ), true ) ;
                foreach( $table_arr as $row )
                {
                    $match = false;
                    foreach( $this->data as $selected_record )
                    {
                        if ( $row == $selected_record )
                        {
                            $match = true;
                        }
                    }
                    if ($match == false) {
                        $new_records[] = $row ;
                    }
                }
                foreach( $this->updated as $updated_record )
                {
                    $new_records[] = $updated_record ;
                }
            } elseif ( !empty( $this->deleted ) && is_array( $this->deleted ) && count( $this->deleted ) > 0 ) {
                $new_records = $this->deleted ;
            } else { echo "failed here"; }
            unset( $this->updated ) ;
            unset( $this->deleted ) ;
            
            if ( is_array( $new_records ) && count( $new_records ) > 0 )
            return file_put_contents( $this->path . "/" . $this->table . ".json", json_encode( $new_records ) ) ? true : false ;
            else return false ;
        } else return false ;
    }
    public function delete( $table , $where )
    {
        $this->result = false ;
        $this->table = $table ;
        if ( is_readable( $this->path . "/" . $this->table . ".json" ) ) 
        {
            $table_arr = json_decode( file_get_contents( $this->path . "/" . $this->table . ".json" ), true ) ;
            $this->data = $table_arr ;
            $this->result = true ;
            
            $keys = array_keys( $where );
            for( $i = 0; $i < count( $table_arr ); ++$i )
            {
                $next = true ;
                while ( $next === true )
                {
                    foreach ( $where as $key => $value )
                    {
                        if ( array_key_exists( $key, $table_arr[$i] ) && $table_arr[$i][$key] == $value ) {
                            $next = true;
                        } else {
                            break 2 ;
                        }
                    }
                    unset($table_arr[$i]);
                    $next = false ;
                }
            }

            $this->deleted = $table_arr ;
            return $this ;
        } else return false ;
    }
    public function distinct()
    {
        if ( isset( $this->key ) && !empty( $this->key ) && is_string( $this->key ) && is_array( $this->data ) )
		{
			foreach( $this->data as $row )
			$result[] = $row[$this->key] ;
			return array_unique( $result ) ;
		} else return false ;
    }
}
/* 
//################ Usage ################
// create an instance and set the path to the database, defaults to this directory.
$db = new db( 'db' ) ;

// prepare a new user record
$newuser['username'] = 'chrisdlangton' ;
$newuser['email'] = 'chris@codewiz.biz' ;
$newuser['active'] = true ;
//or
$newuser = array(
            'username' => 'chrisdlangton'
            ,'email' => 'chris@codewiz.biz'
            ,'active' => true
            ) ;
// insert a new user (will also create the 'table')
$db->insert( 'users' , $newuser ) ;

// query all records in 'table'
$db->select( 'users' )->get() ;

// query all records in 'table' that have a 'column' matching x but the value of column x may be anything as long as column x exists
$db->select( 'users' , 'email' )->get() ;

// query all records in 'table' that have a 'column' with values matching an array of columns and values.
$db->select( 'users', array( 'username'=>'chrisdlangton' , 'active'=>true , 'email'=>'chris@codewiz.biz' ) )->get() ;

// prepare an update, use get() to view the results of the select
$db->select( 'users' , 'email' )->update( array( 'username'=>'testing' ) )->get() ;

// prepare an update, use get( 'update' ) to view the results of the update before committing
$db->select( 'users' , 'email' )->update( array( 'username'=>'testing' ) )->get( 'update' ) ;

// prepare an update, use commit() to commit the changes to the db
$db->select( 'users' , 'email' )->update( array( 'username'=>'testing' ) )->commit() ;
// or
$db->select( 'users' , 'email' ) ;
$db->update( array( 'username'=>'testing' ) ) ;
$db->commit() ;

// prepare an update with multiple changes, use commit() to commit the changes to the db
$db->select( 'users' , 'email' )->update( array( 'username'=>'testing','active'=>false ) )->commit() ;
// or
$db->select( 'users' , 'email' ) ;
$db->update( array( 'username'=>'testing','active'=>false ) ) ;
$db->commit() ;

// select by primary key id
$db->select( 'users' )->id( 5 ) ;

// select all distict values in a table by column name
$db->select( 'users', 'email' )->distinct() ;

// prepare a delete, use get() to view the original table data
$db->delete( 'users' , array( 'username'=>'testing' ) )->get() ;

// prepare a delete, use get( 'delete' ) to view the new table data before commiting
$db->delete( 'users' , array( 'username'=>'testing' ) )->get( 'delete' ) ;

// prepare a delete, use commit() to commit the changes to the db
$db->delete( 'users' , array( 'username'=>'testing' ) )->commit() ;
// or
$db->delete( 'users' , array( 'username'=>'testing' ) ) ;
$db->commit() ;

// prepare an update with multiple changes, use commit() to commit the changes to the db
$db->delete( 'users' , array( 'active'=>true , 'username'=>'chrisdlangton' ) )->commit() ;
// or
$db->delete( 'users' , array( 'active'=>true , 'username'=>'chrisdlangton' ) ) ;
$db->commit() ;

 */