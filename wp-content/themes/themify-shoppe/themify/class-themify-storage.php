<?php
class Themify_Storage {
    
    private static $table=null;
    
    public static function init(){
	if(self::$table===null){
	    global $wpdb;	    
	    $errors = $wpdb->show_errors;
	    try{		
		self::$table=$wpdb->prefix.'tf_storage';
		$q='CREATE TABLE IF NOT EXISTS '.self::$table.' ( 
		    `key` CHAR(16) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL PRIMARY KEY,
		    `value` MEDIUMTEXT NOT NULL,
		    `expire` INT UNSIGNED,
		    KEY(expire)
		) ENGINE=InnoDB '.$wpdb->get_charset_collate().';';
		$wpdb->hide_errors();
		$res= $wpdb->query($q);	
		if($res===false){
		    self::$table=false;
		}
		unset($q,$res);
	    }
	    catch(Exception $e){
		self::$table=false;
	    }
	    finally {
		if ( $errors ) {
		    $wpdb->show_errors();
		}
	    }
	}
	return self::$table;
    }
    public static function cleanDb(){
	if(self::init()!==false){
	    $q='DELETE FROM %s WHERE `expire` IS NOT NULL AND `expire`<'.time();
	    return self::query($q);
	}
	return false;
    }
    
    public static function query($q){
	if(self::init()!==false){
	    global $wpdb;
	    return $wpdb->query(sprintf($q,self::$table));
	}
	return false;
    }
    
    public static function get($key,$prefix=null,$from=null){
	$k=self::getHash($key,$prefix);	
	if($from!=='f' && self::init()!==false){
	    global $wpdb;
	    $q='SELECT `value`,`expire` FROM '.self::$table.' WHERE `key`="'.esc_sql($k).'" LIMIT 1';
	    $res= $wpdb->get_row($q,ARRAY_A );
	    unset($k,$q);
	}
	elseif($from===null || $from==='db'){
	    return get_transient($k);
	}
	elseif($from!=='db'){
	    $dir=themify_upload_dir('basedir').'/tf_storage/';
	    $found='';
	    $k='@@@'.$k;	
	    $len=strlen($k);
	    for($i=1;$i<10;++$i){
		$f=$dir.$i.'.txt';
		if (Themify_Filesystem::is_file($f) && ($data=file_get_contents($f))) {
		    $start = strpos($data,$k);
		    if($start!==false){
			$j=$start+$len;
			while(true){
			    if(!isset($data[$j]) || (isset($data[$j+1],$data[$j+2]) && $data[$j]==='@' && $data[$j+1]==='@' && $data[$j+2]==='@')){
				break;
			    }
			    $found.=$data[$j];
			    ++$j;
			}
			unset($data,$start,$f,$dir);
			break;
		    }
		}
	    }
	    if($found!==''){
		$res=array();
		if(strpos($found,'tfE==')!==false){
		    $expire=explode('tfE==',$found,2);
		    $found=$expire[0];
		    $res['expire']=$expire[1];
		    unset($expire);
		}
		
		if(strpos($found,'tfV==')!==false){
		    $res['value']=explode('tfV==',$found,2)[1];
		}
		unset($found);
	    }
	}
	if(!empty($res)){
	    if(!empty($res['expire']) && time()>$res['expire']){
		self::delete($key,$prefix,$from);
		return false;
	    }
	    if(isset($res['value'])){
		return $res['value'];
	    }
	}
	return false;
    }
    
    public static function set($key,$v,$exp=null,$prefix=null,$to=null){
	$k=self::getHash($key,$prefix);
	if(is_array($v)){
	    $v= json_encode($v);
	}
	elseif($v===true || $v===false){
	    $v=$v===true?'1':'0';
	}
	if($to!=='f' && self::init()!==false){
	    global $wpdb;
	    if($exp===null){
		$exp='DEFAULT';
	    }
	    else{
		$exp=(int)$exp+time();		
		$exp= '"'.esc_sql($exp).'"';
	    }
	    $v=esc_sql($v);
	    $q='INSERT INTO '.self::$table.' (`key`,`value`,`expire`) VALUES ("'.esc_sql($k).'","'.$v.'",'.$exp.') ON DUPLICATE KEY UPDATE `value`="'.$v.'",`expire`='.$exp;
	    unset($k,$exp);
	    return $wpdb->query($q);
	}
	elseif($exp!==null || $to==='db'){
	   return set_transient($k, esc_sql($v),$exp);
	}
	elseif($to!=='db'){
	    $dir=themify_upload_dir('basedir').'/tf_storage/';
            $max=1048576;//1mb
            $f='';
            clearstatcache();//get correct file size
            //we are storing the sizes in files with 1mb,the speed of reading/writing file will be faster instead of storing all in 1 file.
            for($i=1;$i<10;++$i){
                $f=$dir.$i.'.txt';
                if(!Themify_Filesystem::is_file($f) || filesize($f)<$max){
                    break;
                }
            }
            if($f==='' || !Themify_Filesystem::mkdir($dir)){
		return false;
            }  
	    $k='@@@'.$k;
            if(Themify_Filesystem::is_file($f) && ($data=file_get_contents($f))){//maybe another php script has already written the data?
		if(strpos($data,$k)!==false){
		    return true;
		}
		unset($data);
            }
	    if($v!==''){
		$k.='tfV=='.$v;
	    }
	    if($exp!==''){
		$k.='tfE=='.$exp;
	    }
            return file_put_contents($f,$k,FILE_APPEND|LOCK_EX )!==false;
	}
	return false;
    }
    
    public static function delete($k,$prefix=null,$from=null){
	$k=self::getHash($k,$prefix);
	if($from!=='f' && self::init()!==false){
	    global $wpdb;
	    $q='DELETE FROM '.self::$table.' WHERE `key`="'.esc_sql($k).'" LIMIT 1';
	    return $wpdb->query($q);
	}
	elseif($from!=='f'){
	    return delete_transient( $k );
	}
	elseif($from!=='db'){
	    $dir=themify_upload_dir('basedir').'/tf_storage/';
	    $k='@@@'.$k;	    
	    for($i=1;$i<10;++$i){
		$f=$dir.$i.'.txt';
		if (Themify_Filesystem::is_file($f) && ($data=file_get_contents($f)) && strpos($data,$k)!==false) {
		    $data=preg_replace('/'.$k.'.*?@@@/u','@@@',$data,1);
		    return file_put_contents($f,trim($data),LOCK_EX )!==false;
		}
	    }
	}
    }
    
    public static function getHash($k,$prefix=null){
	static $h=null;
	if($h===null){
	    $hashs= hash_algos();
	    $h='fnv164';
	    if(in_array('xxh3',$hashs,true)){
		$h='xxh3';
	    }
	    elseif(in_array('fnv1a64',$hashs,true)){
		$h='fnv1a64';
	    }
	    unset($hashs);
	}
	$k=hash($h,$k);
	if($prefix!==null){
	    $k=substr_replace($k,$prefix,0,strlen($prefix));
	}
	return $k;
    }
    
    public static function deleteByPrefix($prefix){
	return self::query('DELETE FROM %s WHERE `key` LIKE "'.esc_sql($prefix).'%%'.'"');
    }
}
